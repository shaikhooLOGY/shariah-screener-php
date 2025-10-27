<?php
namespace App\Controllers\Mufti;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;

class SuggestionsController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'ratios.review_suggestion')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get suggestions assigned to this mufti
        $stmt = $pdo->prepare("
            SELECT rs.*, c.ticker, c.name as company_name, u.name as suggester_name
            FROM ratio_suggestions rs
            LEFT JOIN companies c ON c.id = rs.company_id
            LEFT JOIN users u ON u.id = rs.suggested_by
            WHERE rs.reviewer_id = ? AND rs.status IN ('pending', 'in_review')
            ORDER BY rs.created_at ASC
        ");
        $stmt->execute([$user['id']]);
        $suggestions = $stmt->fetchAll();

        $this->view('mufti/suggestions/index', [
            'suggestions' => $suggestions
        ]);
    }

    public function accept(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'ratios.review_suggestion')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $suggestionId = (int)($input['suggestion_id'] ?? 0);
        $note = trim($input['note'] ?? '');

        if (!$suggestionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing suggestion ID']);
            return;
        }

        $pdo = db_pdo();

        // Verify suggestion is assigned to this mufti
        $stmt = $pdo->prepare("SELECT * FROM ratio_suggestions WHERE id = ? AND reviewer_id = ?");
        $stmt->execute([$suggestionId, $user['id']]);
        $suggestion = $stmt->fetch();

        if (!$suggestion) {
            http_response_code(404);
            echo json_encode(['error' => 'Suggestion not found or not assigned to you']);
            return;
        }

        // Accept suggestion and write to financial_series
        $payload = json_decode($suggestion['payload_json'], true);

        foreach ($payload as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO financial_series (company_id, period, key, value, source, by_user, confidence)
                VALUES (?, ?, ?, ?, 'suggestion', ?, 'med')
            ");
            $stmt->execute([$suggestion['company_id'], $suggestion['period'], $key, $value, $user['id']]);
        }

        // Update suggestion status
        $stmt = $pdo->prepare("UPDATE ratio_suggestions SET status = 'accepted', review_note = ?, reviewed_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$note, $suggestionId]);

        audit_log($user['id'], 'suggestion.accept', 'ratio_suggestions', $suggestionId, [
            'company_id' => $suggestion['company_id'],
            'period' => $suggestion['period'],
            'note' => $note
        ]);

        echo json_encode(['success' => true]);
    }

    public function reject(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'ratios.review_suggestion')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $suggestionId = (int)($input['suggestion_id'] ?? 0);
        $note = trim($input['note'] ?? '');

        if (!$suggestionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing suggestion ID']);
            return;
        }

        $pdo = db_pdo();

        // Verify suggestion is assigned to this mufti
        $stmt = $pdo->prepare("SELECT * FROM ratio_suggestions WHERE id = ? AND reviewer_id = ?");
        $stmt->execute([$suggestionId, $user['id']]);
        $suggestion = $stmt->fetch();

        if (!$suggestion) {
            http_response_code(404);
            echo json_encode(['error' => 'Suggestion not found or not assigned to you']);
            return;
        }

        // Reject suggestion
        $stmt = $pdo->prepare("UPDATE ratio_suggestions SET status = 'rejected', review_note = ?, reviewed_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$note, $suggestionId]);

        audit_log($user['id'], 'suggestion.reject', 'ratio_suggestions', $suggestionId, [
            'company_id' => $suggestion['company_id'],
            'period' => $suggestion['period'],
            'note' => $note
        ]);

        echo json_encode(['success' => true]);
    }
}