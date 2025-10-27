<?php
namespace App\Controllers\Superadmin;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;
use function set_flash;
use function redirect_for_role;

class ApprovalsController extends Controller
{
    public function approve(int $id): void
    {
        $this->handleDecision($id, 'approved');
    }

    public function reject(int $id): void
    {
        $this->handleDecision($id, 'rejected');
    }

    private function handleDecision(int $id, string $decision): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'role.approve_request')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get approval details
        $stmt = $pdo->prepare('SELECT * FROM approvals WHERE id = ?');
        $stmt->execute([$id]);
        $approval = $stmt->fetch();

        if (!$approval) {
            http_response_code(404);
            echo json_encode(['error' => 'Approval not found']);
            return;
        }

        if ($approval['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode(['error' => 'Approval already decided']);
            return;
        }

        // Update approval
        $stmt = $pdo->prepare('UPDATE approvals SET status = ?, approver_id = ?, decided_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$decision, $user['id'], $id]);

        // Handle specific approval types
        if ($approval['kind'] === 'user.ban' && $decision === 'approved') {
            // Actually ban the user
            $payload = json_decode($approval['payload_json'], true);
            $targetUserId = $payload['target_user']['id'] ?? null;

            if ($targetUserId) {
                $stmt = $pdo->prepare('UPDATE users SET active = 0 WHERE id = ?');
                $stmt->execute([$targetUserId]);

                // Audit log for the actual ban
                audit_log($user['id'], 'user.ban', 'users', $targetUserId, [
                    'approval_id' => $id,
                    'reason' => $approval['note']
                ]);
            }
        } elseif ($approval['kind'] === 'cmv.publish' && $decision === 'approved') {
            // Publish CMV
            $payload = json_decode($approval['payload_json'], true);
            $cmvId = $payload['cmv']['id'] ?? null;

            if ($cmvId) {
                $pdo->beginTransaction();
                try {
                    // Archive current published CMV if exists
                    $stmt = $pdo->prepare("UPDATE compliance_master_versions SET status = 'archived' WHERE status = 'published'");
                    $stmt->execute();

                    // Publish new CMV
                    $stmt = $pdo->prepare("UPDATE compliance_master_versions SET status = 'published' WHERE id = ?");
                    $stmt->execute([$cmvId]);

                    // Update current pointers
                    $stmt = $pdo->prepare("UPDATE compliance_master_current SET cmv_id_published = ?, cmv_id_pending = NULL WHERE id = 1");
                    $stmt->execute([$cmvId]);

                    $pdo->commit();

                    audit_log($user['id'], 'cmv.publish.approved', 'compliance_master_versions', $cmvId, [
                        'approval_id' => $id
                    ]);
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            }
        } elseif ($approval['kind'] === 'cmv.rollback' && $decision === 'approved') {
            // Rollback CMV
            $payload = json_decode($approval['payload_json'], true);
            $cmvId = $payload['cmv']['id'] ?? null;

            if ($cmvId) {
                $pdo->beginTransaction();
                try {
                    // Find the previous archived/published CMV
                    $stmt = $pdo->prepare("
                        SELECT id FROM compliance_master_versions
                        WHERE status IN ('archived', 'published') AND id != ?
                        ORDER BY created_at DESC LIMIT 1
                    ");
                    $stmt->execute([$cmvId]);
                    $previousCmv = $stmt->fetch();

                    if ($previousCmv) {
                        // Archive current published
                        $stmt = $pdo->prepare("UPDATE compliance_master_versions SET status = 'archived' WHERE status = 'published'");
                        $stmt->execute();

                        // Publish previous
                        $stmt = $pdo->prepare("UPDATE compliance_master_versions SET status = 'published' WHERE id = ?");
                        $stmt->execute([$previousCmv['id']]);

                        // Update pointers
                        $stmt = $pdo->prepare("UPDATE compliance_master_current SET cmv_id_published = ?, cmv_id_pending = NULL WHERE id = 1");
                        $stmt->execute([$previousCmv['id']]);
                    }

                    // Mark rolled back CMV as rolled_back
                    $stmt = $pdo->prepare("UPDATE compliance_master_versions SET status = 'rolled_back' WHERE id = ?");
                    $stmt->execute([$cmvId]);

                    $pdo->commit();

                    audit_log($user['id'], 'cmv.rollback.approved', 'compliance_master_versions', $cmvId, [
                        'approval_id' => $id,
                        'restored_cmv_id' => $previousCmv['id'] ?? null
                    ]);
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            }
        }

        // Audit log for approval decision
        audit_log($user['id'], 'approval.' . $decision, 'approvals', $id, [
            'kind' => $approval['kind'],
            'entity' => $approval['entity'],
            'entity_id' => $approval['entity_id']
        ]);

        echo json_encode(['status' => $decision, 'approval_id' => $id]);
    }
}