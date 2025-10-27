<?php
namespace App\Controllers\Admin;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;
use function set_flash;
use function redirect_for_role;

class ApprovalsController extends Controller
{
    public function create(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'user.ban')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int)($input['user_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$userId || !$reason) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing user_id or reason']);
            return;
        }

        // Verify the target user exists
        $pdo = db_pdo();
        $stmt = $pdo->prepare('SELECT id, name, active FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        if ($targetUser['active'] == 0) {
            http_response_code(400);
            echo json_encode(['error' => 'User already banned']);
            return;
        }

        // Create approval request
        $stmt = $pdo->prepare('INSERT INTO approvals (kind, entity, entity_id, requested_by, payload_json, note) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            'user.ban',
            'users',
            $userId,
            $user['id'],
            json_encode(['target_user' => $targetUser]),
            $reason
        ]);

        $approvalId = $pdo->lastInsertId();

        // Audit log
        audit_log($user['id'], 'approval.create', 'approvals', $approvalId, [
            'kind' => 'user.ban',
            'target_user_id' => $userId,
            'target_user_name' => $targetUser['name']
        ]);

        http_response_code(201);
        echo json_encode(['approval_id' => $approvalId, 'status' => 'pending']);
    }
}