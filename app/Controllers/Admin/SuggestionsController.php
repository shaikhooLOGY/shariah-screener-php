<?php
namespace App\Controllers\Admin;

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

        // Get pending suggestions with company info
        $stmt = $pdo->query("
            SELECT rs.*, c.ticker, c.name as company_name, u.name as suggester_name
            FROM ratio_suggestions rs
            LEFT JOIN companies c ON c.id = rs.company_id
            LEFT JOIN users u ON u.id = rs.suggested_by
            WHERE rs.status = 'pending'
            ORDER BY rs.created_at ASC
        ");
        $suggestions = $stmt->fetchAll();

        $this->view('admin/suggestions/index', [
            'suggestions' => $suggestions
        ]);
    }

    public function openCompare(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'ratios.review_suggestion')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $suggestionId = (int)($_GET['id'] ?? 0);

        if (!$suggestionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing suggestion ID']);
            return;
        }

        $pdo = db_pdo();

        // Get suggestion with company info
        $stmt = $pdo->prepare("
            SELECT rs.*, c.ticker, c.name as company_name, u.name as suggester_name
            FROM ratio_suggestions rs
            LEFT JOIN companies c ON c.id = rs.company_id
            LEFT JOIN users u ON u.id = rs.suggested_by
            WHERE rs.id = ?
        ");
        $stmt->execute([$suggestionId]);
        $suggestion = $stmt->fetch();

        if (!$suggestion) {
            http_response_code(404);
            echo json_encode(['error' => 'Suggestion not found']);
            return;
        }

        // Get current CMV data for comparison
        $stmt = $pdo->prepare("
            SELECT cmv.label as cmv_label, cmv.period as cmv_period, cr.final_score, cr.verdict
            FROM compliance_master_current cmc
            JOIN compliance_master_versions cmv ON cmv.id = cmc.cmv_id_published
            LEFT JOIN cmv_results cr ON cr.cmv_id = cmv.id AND cr.company_id = ?
            WHERE cmc.id = 1
        ");
        $stmt->execute([$suggestion['company_id']]);
        $cmvData = $stmt->fetch();

        // Get latest filing data
        $stmt = $pdo->prepare("
            SELECT * FROM filings
            WHERE company_id = ?
            ORDER BY filing_date DESC
            LIMIT 1
        ");
        $stmt->execute([$suggestion['company_id']]);
        $filing = $stmt->fetch();

        echo json_encode([
            'suggestion' => $suggestion,
            'cmv' => $cmvData,
            'filing' => $filing
        ]);
    }

    public function assignReviewer(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'ratios.review_suggestion')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $suggestionId = (int)($input['suggestion_id'] ?? 0);

        if (!$suggestionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing suggestion ID']);
            return;
        }

        $pdo = db_pdo();

        // Assign current user as reviewer
        $stmt = $pdo->prepare("UPDATE ratio_suggestions SET reviewer_id = ?, status = 'in_review' WHERE id = ?");
        $stmt->execute([$user['id'], $suggestionId]);

        audit_log($user['id'], 'suggestion.assign', 'ratio_suggestions', $suggestionId, [
            'reviewer_id' => $user['id']
        ]);

        echo json_encode(['success' => true]);
    }
}