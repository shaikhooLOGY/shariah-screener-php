<?php
namespace App\Controllers\Superadmin;

use PDO;
use function audit_log;

class SystemController extends BaseController
{
    public function index(): void
    {
        $this->requireSuperadmin();
        $this->redirect('/dashboard/superadmin/system');
    }

    public function system(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();
        $flash = $this->takeFlash();
        $flags = $pdo->query('SELECT key, value, label, updated_at FROM feature_flags ORDER BY key')->fetchAll(PDO::FETCH_ASSOC);

        $health = $this->healthStatus();
        $recentLogs = $this->recentAuditLogs();

        $this->view('superadmin/system', [
            'title' => 'Superadmin · System',
            'flash' => $flash,
            'flags' => $flags,
            'health' => $health,
            'logs' => $recentLogs,
            'csrf' => $_SESSION['csrf'] ?? '',
        ]);
    }

    public function toggleFlag($key): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();

        $pdo = $this->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($key === 'ui.skin') {
            // Special handling for UI skin
            $skinValue = trim((string)($_POST['skin_value'] ?? 'classic'));
            if (!in_array($skinValue, ['classic', 'aurora', 'noor'])) {
                $skinValue = 'classic';
            }
            // Update .env file
            $envFile = app_root() . '/.env';
            if (is_file($envFile)) {
                $envContent = file_get_contents($envFile);
                $envContent = preg_replace('/^UI_SKIN=.*/m', "UI_SKIN={$skinValue}", $envContent);
                if (!preg_match('/^UI_SKIN=/m', $envContent)) {
                    $envContent .= "\nUI_SKIN={$skinValue}";
                }
                file_put_contents($envFile, $envContent);
            }
            audit_log($this->actorId(), 'ui.skin.change', 'config', 'ui.skin', ['skin' => $skinValue]);
            $this->flash('success', sprintf('UI skin changed to %s.', ucfirst($skinValue)));
            $this->redirect('/dashboard/superadmin/system');
            return;
        }

        $value = isset($_POST['value']) ? (int)$_POST['value'] : 0;
        $label = trim((string)($_POST['label'] ?? $key));

        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('INSERT INTO feature_flags (key, value, label, updated_at) VALUES (:key, :value, :label, CURRENT_TIMESTAMP)
                ON CONFLICT(key) DO UPDATE SET value = excluded.value, label = excluded.label, updated_at = CURRENT_TIMESTAMP');
        } else {
            $stmt = $pdo->prepare('INSERT INTO feature_flags (key, value, label, updated_at) VALUES (:key, :value, :label, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE value = VALUES(value), label = VALUES(label), updated_at = CURRENT_TIMESTAMP');
        }
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
            ':label' => $label,
        ]);
        audit_log($this->actorId(), 'feature.toggle', 'feature_flags', $key, ['value' => $value]);
        $this->flash('success', sprintf('%s flag %s ho gaya.', $key, $value ? 'ON' : 'OFF'));
        $this->redirect('/dashboard/superadmin/system');
    }

    private function healthStatus(): array
    {
        $pdo = $this->pdo();
        $start = microtime(true);
        $status = 'ok';
        $error = null;
        try {
            $pdo->query('SELECT 1');
        } catch (\Throwable $e) {
            $status = 'fail';
            $error = $e->getMessage();
        }
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        return [
            'status' => $status,
            'db_time' => $elapsed,
            'error' => $error,
            'env' => $_ENV['APP_ENV'] ?? 'n/a',
        ];
    }

    private function recentAuditLogs(): array
    {
        $stmt = $this->pdo()->query('SELECT al.id, al.action, al.entity, al.entity_id, al.meta, al.created_at, u.name AS actor_name
            FROM audit_log al LEFT JOIN users u ON u.id = al.actor_id ORDER BY al.id DESC LIMIT 5');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['meta'] = $row['meta'] ? json_decode($row['meta'], true) : [];
        }
        return $rows;
    }
}
