<?php
namespace App\Controllers\Superadmin;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;

class ControversyController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'controversy.finalize')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get all controversies with vote counts
        $stmt = $pdo->query("
            SELECT cq.*,
                   c.ticker, c.name as company_name,
                   COUNT(cv.id) as vote_count,
                   COUNT(CASE WHEN cv.vote = 'compliant' THEN 1 END) as compliant_votes,
                   COUNT(CASE WHEN cv.vote = 'noncompliant' THEN 1 END) as noncompliant_votes,
                   COUNT(CASE WHEN cv.vote = 'grey' THEN 1 END) as grey_votes
            FROM controversy_queue cq
            LEFT JOIN companies c ON c.id = cq.company_id
            LEFT JOIN controversy_votes cv ON cv.controversy_id = cq.id
            GROUP BY cq.id
            ORDER BY cq.opened_at DESC
        ");
        $controversies = $stmt->fetchAll();

        // Get config for thresholds
        $minVotes = 3; // controversy.min_votes
        $minAgree = 2; // controversy.min_agree

        $this->view('superadmin/controversies/index', [
            'controversies' => $controversies,
            'min_votes' => $minVotes,
            'min_agree' => $minAgree
        ]);
    }

    public function finalize(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'controversy.finalize')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $controversyId = (int)($input['controversy_id'] ?? 0);

        if (!$controversyId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing controversy ID']);
            return;
        }

        $pdo = db_pdo();

        // Get controversy with vote counts
        $stmt = $pdo->prepare("
            SELECT cq.*,
                   COUNT(cv.id) as vote_count,
                   COUNT(CASE WHEN cv.vote = 'compliant' THEN 1 END) as compliant_votes,
                   COUNT(CASE WHEN cv.vote = 'noncompliant' THEN 1 END) as noncompliant_votes,
                   COUNT(CASE WHEN cv.vote = 'grey' THEN 1 END) as grey_votes
            FROM controversy_queue cq
            LEFT JOIN controversy_votes cv ON cv.controversy_id = cq.id
            WHERE cq.id = ?
            GROUP BY cq.id
        ");
        $stmt->execute([$controversyId]);
        $controversy = $stmt->fetch();

        if (!$controversy) {
            http_response_code(404);
            echo json_encode(['error' => 'Controversy not found']);
            return;
        }

        if ($controversy['status'] !== 'open') {
            http_response_code(400);
            echo json_encode(['error' => 'Controversy already closed']);
            return;
        }

        // Check if ready for finalization (simplified logic)
        $totalVotes = $controversy['vote_count'];
        $maxVoteType = max($controversy['compliant_votes'], $controversy['noncompliant_votes'], $controversy['grey_votes']);

        if ($totalVotes < 3 || $maxVoteType < 2) {
            http_response_code(400);
            echo json_encode(['error' => 'Not enough votes or agreement to finalize']);
            return;
        }

        // Determine final stance
        if ($controversy['compliant_votes'] >= $controversy['noncompliant_votes'] && $controversy['compliant_votes'] >= $controversy['grey_votes']) {
            $finalStance = 'compliant';
        } elseif ($controversy['noncompliant_votes'] >= $controversy['grey_votes']) {
            $finalStance = 'noncompliant';
        } else {
            $finalStance = 'grey';
        }

        // Close controversy and create activity history
        $pdo->beginTransaction();
        try {
            // Close controversy
            $stmt = $pdo->prepare("UPDATE controversy_queue SET status = 'closed', closed_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$controversyId]);

            // Create activity history entry
            $stmt = $pdo->prepare("
                INSERT INTO activity_history (company_id, snapshot_json, created_by)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $controversy['company_id'],
                json_encode([
                    'type' => 'controversy_finalized',
                    'controversy_id' => $controversyId,
                    'topic' => $controversy['topic'],
                    'final_stance' => $finalStance,
                    'vote_summary' => [
                        'total_votes' => $totalVotes,
                        'compliant' => $controversy['compliant_votes'],
                        'noncompliant' => $controversy['noncompliant_votes'],
                        'grey' => $controversy['grey_votes']
                    ],
                    'finalized_by' => $user['id'],
                    'finalized_at' => date('Y-m-d H:i:s')
                ]),
                $user['id']
            ]);

            $pdo->commit();

            audit_log($user['id'], 'controversy.finalize', 'controversy_queue', $controversyId, [
                'final_stance' => $finalStance,
                'total_votes' => $totalVotes,
                'company_id' => $controversy['company_id']
            ]);

            echo json_encode(['success' => true, 'final_stance' => $finalStance]);
        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to finalize controversy']);
        }
    }
}