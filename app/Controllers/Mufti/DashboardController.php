<?php
namespace App\Controllers\Mufti;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        if (!$user || !in_array($user['role'], ['mufti', 'admin', 'superadmin'])) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get user's profile
        $stmt = $pdo->prepare("SELECT * FROM mufti_profiles WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $profile = $stmt->fetch();

        // Get assigned tasks
        $stmt = $pdo->prepare("
            SELECT t.*, c.ticker, c.name as company_name
            FROM tasks t
            LEFT JOIN companies c ON c.id = t.company_id
            WHERE t.assignee_id = ?
            ORDER BY t.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user['id']]);
        $recentTasks = $stmt->fetchAll();

        // Get recent reviews (suggestions reviewed)
        $stmt = $pdo->prepare("
            SELECT rs.*, c.ticker, c.name as company_name
            FROM ratio_suggestions rs
            LEFT JOIN companies c ON c.id = rs.company_id
            WHERE rs.reviewer_id = ?
            ORDER BY rs.reviewed_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $recentReviews = $stmt->fetchAll();

        // Get controversies participated in
        $stmt = $pdo->prepare("
            SELECT cq.*, c.ticker, c.name as company_name, cv.vote
            FROM controversy_votes cv
            JOIN controversy_queue cq ON cq.id = cv.controversy_id
            LEFT JOIN companies c ON c.id = cq.company_id
            WHERE cv.mufti_id = ?
            ORDER BY cv.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $recentControversies = $stmt->fetchAll();

        // Get activity history entries created by user
        $stmt = $pdo->prepare("
            SELECT ah.*, c.ticker, c.name as company_name
            FROM activity_history ah
            LEFT JOIN companies c ON c.id = ah.company_id
            WHERE ah.created_by = ?
            ORDER BY ah.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $recentActivity = $stmt->fetchAll();

        $this->view('mufti/dashboard', [
            'profile' => $profile,
            'recent_tasks' => $recentTasks,
            'recent_reviews' => $recentReviews,
            'recent_controversies' => $recentControversies,
            'recent_activity' => $recentActivity
        ]);
    }

    public function updateProfile(): void
    {
        $user = current_user();
        if (!$user || !in_array($user['role'], ['mufti', 'admin', 'superadmin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $expertiseSectors = $input['expertise_sectors'] ?? [];
        $bio = trim($input['bio'] ?? '');

        $pdo = db_pdo();

        // Upsert profile
        $stmt = $pdo->prepare("
            INSERT INTO mufti_profiles (user_id, expertise_sectors_json, bio, updated_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ON CONFLICT(user_id) DO UPDATE SET
            expertise_sectors_json = excluded.expertise_sectors_json,
            bio = excluded.bio,
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$user['id'], json_encode($expertiseSectors), $bio]);

        echo json_encode(['success' => true]);
    }
}