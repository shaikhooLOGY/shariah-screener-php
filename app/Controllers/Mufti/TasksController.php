<?php
namespace App\Controllers\Mufti;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;

class TasksController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'activity.update')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get user's tasks grouped by status
        $stmt = $pdo->prepare("
            SELECT t.*, c.ticker, c.name as company_name
            FROM tasks t
            LEFT JOIN companies c ON c.id = t.company_id
            WHERE t.assignee_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $tasks = $stmt->fetchAll();

        $grouped = ['open' => [], 'in_progress' => [], 'done' => [], 'blocked' => []];
        foreach ($tasks as $task) {
            $grouped[$task['status']][] = $task;
        }

        $this->view('mufti/tasks/index', [
            'grouped_tasks' => $grouped
        ]);
    }

    public function updateStatus(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'activity.update')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $taskId = (int)($input['task_id'] ?? 0);
        $newStatus = $input['status'] ?? '';

        if (!$taskId || !in_array($newStatus, ['open', 'in_progress', 'done', 'blocked'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid task_id or status']);
            return;
        }

        $pdo = db_pdo();

        // Verify task belongs to user
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assignee_id = ?");
        $stmt->execute([$taskId, $user['id']]);
        $task = $stmt->fetch();

        if (!$task) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found or not assigned to you']);
            return;
        }

        // Update task
        $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$newStatus, $taskId]);

        // If completing task, also update activity if it's an activity_review
        if ($newStatus === 'done' && $task['type'] === 'activity_review') {
            $this->completeActivityReview($pdo, $task, $user['id']);
        }

        audit_log($user['id'], 'task.status_update', 'tasks', $taskId, [
            'old_status' => $task['status'],
            'new_status' => $newStatus,
            'type' => $task['type']
        ]);

        echo json_encode(['success' => true, 'new_status' => $newStatus]);
    }

    private function completeActivityReview($pdo, $task, $userId): void
    {
        $payload = json_decode($task['payload_json'], true);

        // Create activity history entry
        $stmt = $pdo->prepare("
            INSERT INTO activity_history (company_id, snapshot_json, created_by)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $task['company_id'],
            json_encode([
                'task_id' => $task['id'],
                'type' => 'activity_review_completion',
                'completed_by' => $userId,
                'completed_at' => date('Y-m-d H:i:s'),
                'payload' => $payload
            ]),
            $userId
        ]);

        audit_log($userId, 'activity.update', 'companies', $task['company_id'], [
            'task_id' => $task['id'],
            'activity_type' => 'review_completion'
        ]);
    }
}