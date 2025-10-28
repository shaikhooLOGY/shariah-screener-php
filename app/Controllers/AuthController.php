<?php
namespace App\Controllers;

use Core\Controller;
use function db_pdo;
use function auth_role;
use function auth_user;
use function set_flash;
use function take_flash;
use function redirect_for_role;

class AuthController extends Controller
{
    public function show(): void
    {
        if (auth_user()) {
            $this->redirect(redirect_for_role(auth_role()));
            return;
        }
        $flash = take_flash();
        $this->view('auth/login', [
            'title' => 'Sign in',
            'csrf' => $_SESSION['csrf'] ?? '',
            'flash' => $flash,
        ]);
    }

    public function login(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $token = $_POST['csrf'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
            set_flash('danger', 'Session expire ho gayi, dubara login karein.');
            $this->redirect('/login');
            return;
        }

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            set_flash('danger', 'Email aur password zaroori hain.');
            $this->redirect('/login');
            return;
        }

        $pdo = db_pdo();
        $stmt = $pdo->prepare('SELECT id, name, email, COALESCE(password_hash, password) AS password_hash, role, active FROM users WHERE LOWER(email) = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !$user['active'] || !password_verify($password, $user['password_hash'])) {
            set_flash('danger', 'Galat credentials ya inactive account.');
            $this->redirect('/login');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['csrf'] = bin2hex(random_bytes(16));

        $redirect = $_POST['redirect_to'] ?? ($_SESSION['intended'] ?? redirect_for_role($user['role']));
        unset($_SESSION['intended']);
        $this->redirect($redirect ?: redirect_for_role($user['role']));
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/');
            return;
        }
        $token = $_POST['csrf'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
            set_flash('danger', 'Session expire ho gayi.');
            $this->redirect('/login');
            return;
        }
        session_regenerate_id(true);
        unset($_SESSION['user'], $_SESSION['user_id'], $_SESSION['role']);
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        set_flash('info', 'Safely logged out.');
        $this->redirect('/login');
    }

    public function impersonate(string $role): void
    {
        // Only allow in development
        if (($_ENV['APP_ENV'] ?? 'production') !== 'development') {
            http_response_code(403);
            echo '403 Forbidden - Development only';
            exit;
        }

        $allowedRoles = ['superadmin', 'admin', 'mufti', 'user'];
        if (!in_array($role, $allowedRoles, true)) {
            http_response_code(400);
            echo '400 Bad Request - Invalid role';
            exit;
        }

        // Get demo user for this role
        $demoEmails = [
            'superadmin' => 'super@demo.com',
            'admin' => 'admin@demo.com',
            'mufti' => 'mufti@demo.com',
            'user' => 'user@demo.com',
        ];

        $pdo = db_pdo();
        $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE LOWER(email) = :email AND active = 1 LIMIT 1');
        $stmt->execute([':email' => $demoEmails[$role]]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo '404 Not Found - Demo user not found';
            exit;
        }

        // Set session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['csrf'] = bin2hex(random_bytes(16));

        // Redirect to home
        header('Location: /');
        exit;
    }

    private function redirect(string $url): void
    {
        header('Location: '.$url);
        exit;
    }
}
