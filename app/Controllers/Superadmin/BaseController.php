<?php
namespace App\Controllers\Superadmin;

use Core\Controller;
use PDO;
use function resolve_dsn;
use function role_at_least;
use function audit_log;

abstract class BaseController extends Controller
{
    protected function requireSuperadmin(): void
    {
        if (!role_at_least('superadmin')) {
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }

    protected function pdo(): PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            $dsn  = resolve_dsn($_ENV['DB_DSN'] ?? 'sqlite:./storage/shaikhoology.sqlite');
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';
            $pdo = new PDO($dsn, $user ?: null, $pass ?: null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        return $pdo;
    }

    protected function takeFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function flash(string $tone, string $message): void
    {
        $_SESSION['flash'][$tone] = $message;
    }

    protected function redirect(string $url): void
    {
        header('Location: '.$url);
        exit;
    }

    protected function actorId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }
    }

    protected function assertCsrf(): void
    {
        $token = $_POST['csrf'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            exit;
        }
    }
}
