<?php
namespace App\Controllers\Mufti;

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
        if (!$user || !user_can($user, 'controversy.vote')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get open controversies
        $stmt = $pdo->query("
            SELECT cq.*, c.ticker, c.name as company_name
            FROM controversy_queue cq
            LEFT JOIN companies c ON c.id = cq.company_id
            WHERE cq.status = 'open'
            ORDER BY cq.opened_at DESC
        ");
        $controversies = $stmt->fetchAll();

        // Get user's votes
        $stmt = $pdo->prepare("
            SELECT controversy_id, vote, note
            FROM controversy_votes
            WHERE mufti_id = ?
        ");
        $stmt->execute([$user['id']]);
        $userVotes = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->view('mufti/controversies/index', [
            'controversies' => $controversies,
            'user_votes' => $userVotes
        ]);
    }

    public function vote(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'controversy.vote')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $controversyId = (int)($input['controversy_id'] ?? 0);
        $vote = $input['vote'] ?? '';
        $note = trim($input['note'] ?? '');

        if (!$controversyId || !in_array($vote, ['compliant', 'noncompliant', 'grey'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid controversy_id or vote']);
            return;
        }

        $pdo = db_pdo();

        // Check if controversy exists and is open
        $stmt = $pdo->prepare("SELECT * FROM controversy_queue WHERE id = ? AND status = 'open'");
        $stmt->execute([$controversyId]);
        $controversy = $stmt->fetch();

        if (!$controversy) {
            http_response_code(404);
            echo json_encode(['error' => 'Controversy not found or closed']);
            return;
        }

        // Check if user already voted
        $stmt = $pdo->prepare("SELECT id FROM controversy_votes WHERE controversy_id = ? AND mufti_id = ?");
        $stmt->execute([$controversyId, $user['id']]);
        $existingVote = $stmt->fetch();

        if ($existingVote) {
            // Update existing vote
            $stmt = $pdo->prepare("UPDATE controversy_votes SET vote = ?, note = ?, created_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$vote, $note, $existingVote['id']]);
        } else {
            // Insert new vote
            $stmt = $pdo->prepare("INSERT INTO controversy_votes (controversy_id, mufti_id, vote, note) VALUES (?, ?, ?, ?)");
            $stmt->execute([$controversyId, $user['id'], $vote, $note]);
        }

        audit_log($user['id'], 'controversy.vote', 'controversy_queue', $controversyId, [
            'vote' => $vote,
            'note' => $note
        ]);

        echo json_encode(['success' => true]);
    }
}