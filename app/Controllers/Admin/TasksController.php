<?php
namespace App\Controllers\Admin;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;
use function set_flash;

class TasksController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'task.assign_mufti')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get all tasks with company and assignee info
        $stmt = $pdo->query("
            SELECT t.*, c.ticker, c.name as company_name,
                   u.name as assignee_name, creator.name as creator_name
            FROM tasks t
            LEFT JOIN companies c ON c.id = t.company_id
            LEFT JOIN users u ON u.id = t.assignee_id
            LEFT JOIN users creator ON creator.id = t.created_by
            ORDER BY t.created_at DESC
        ");
        $tasks = $stmt->fetchAll();

        $this->view('admin/tasks/index', [
            'tasks' => $tasks
        ]);
    }

    public function create(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'task.assign_mufti')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $title = trim($input['title'] ?? '');
        $type = $input['type'] ?? 'activity_review';
        $companyId = (int)($input['company_id'] ?? 0);
        $priority = $input['priority'] ?? 'med';
        $payload = $input['payload'] ?? [];

        if (!$title || !$companyId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $pdo = db_pdo();

        // Auto-assign to mufti with least tasks in this sector
        $assigneeId = $this->assignToBestMufti($pdo, $companyId);

        $stmt = $pdo->prepare("
            INSERT INTO tasks (title, type, company_id, payload_json, priority, assignee_id, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, 'open', ?)
        ");
        $stmt->execute([$title, $type, $companyId, json_encode($payload), $priority, $assigneeId, $user['id']]);
        $taskId = $pdo->lastInsertId();

        audit_log($user['id'], 'task.create', 'tasks', $taskId, [
            'title' => $title,
            'type' => $type,
            'company_id' => $companyId,
            'assignee_id' => $assigneeId
        ]);

        echo json_encode(['task_id' => $taskId, 'assignee_id' => $assigneeId]);
    }

    public function update(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'task.update')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $taskId = (int)($input['task_id'] ?? 0);
        $updates = $input['updates'] ?? [];

        if (!$taskId || empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing task_id or updates']);
            return;
        }

        $pdo = db_pdo();
        $allowedFields = ['status', 'priority', 'assignee_id', 'sla_at'];

        $setParts = [];
        $values = [];
        foreach ($updates as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setParts[] = "{$field} = ?";
                $values[] = $value;
            }
        }

        if (empty($setParts)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid updates']);
            return;
        }

        $values[] = $taskId;
        $stmt = $pdo->prepare("UPDATE tasks SET " . implode(', ', $setParts) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute($values);

        audit_log($user['id'], 'task.update', 'tasks', $taskId, [
            'updates' => $updates
        ]);

        echo json_encode(['success' => true]);
    }

    private function assignToBestMufti($pdo, $companyId): ?int
    {
        // Get company sector
        $stmt = $pdo->prepare("SELECT sector FROM companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();

        if (!$company || !$company['sector']) {
            // Assign to any mufti with least tasks
            $stmt = $pdo->query("
                SELECT u.id, COUNT(t.id) as task_count
                FROM users u
                LEFT JOIN tasks t ON t.assignee_id = u.id AND t.status IN ('open', 'in_progress')
                WHERE u.role = 'mufti' AND u.active = 1
                GROUP BY u.id
                ORDER BY task_count ASC
                LIMIT 1
            ");
        } else {
            // Assign to mufti with expertise in this sector and least tasks
            $stmt = $pdo->query("
                SELECT u.id, COUNT(t.id) as task_count
                FROM users u
                LEFT JOIN mufti_profiles mp ON mp.user_id = u.id
                LEFT JOIN tasks t ON t.assignee_id = u.id AND t.status IN ('open', 'in_progress')
                WHERE u.role = 'mufti' AND u.active = 1
                AND (mp.expertise_sectors_json IS NULL OR mp.expertise_sectors_json LIKE '%{$company['sector']}%')
                GROUP BY u.id
                ORDER BY task_count ASC
                LIMIT 1
            ");
        }

        $mufti = $stmt->fetch();
        return $mufti ? (int)$mufti['id'] : null;
    }
}