<?php
namespace App\Controllers\Superadmin;

use PDO;
use function audit_log;

class UsersController extends BaseController
{
    private const PAGE_SIZE = 15;

    public function index(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();
        $flash = $this->takeFlash();

        $query = trim((string)($_GET['q'] ?? ''));
        $roleFilter = trim((string)($_GET['role'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = self::PAGE_SIZE;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(LOWER(name) LIKE :q OR LOWER(email) LIKE :qe)';
            $params[':q'] = '%'.mb_strtolower($query).'%';
            $params[':qe'] = '%'.mb_strtolower($query).'%';
        }
        if ($roleFilter !== '' && in_array($roleFilter, ['user','mufti','admin','superadmin'], true)) {
            $where[] = 'role = :role';
            $params[':role'] = $roleFilter;
        }
        $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $pages = max(1, (int)ceil($total / $limit));
        if ($page > $pages) {
            $page = $pages;
            $offset = ($page - 1) * $limit;
        }

        $sql = "SELECT id, name, email, role, active, created_at FROM users $whereSql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('superadmin/users', [
            'title' => 'Superadmin · Users',
            'users' => $users,
            'filters' => ['q' => $query, 'role' => $roleFilter],
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'limit' => $limit,
            ],
            'flash' => $flash,
            'csrf' => $_SESSION['csrf'] ?? '',
        ]);
    }

    public function updateRole($id): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();
        $targetRole = trim((string)($_POST['role'] ?? ''));
        $allowed = ['user', 'mufti', 'admin'];
        if (!in_array($targetRole, $allowed, true)) {
            $this->flash('danger', 'Role not allowed.');
            $this->redirect('/dashboard/superadmin/users');
        }

        $user = $this->findUser((int)$id);
        if (!$user) {
            http_response_code(404);
            echo 'User not found';
            return;
        }
        if ($user['role'] === 'superadmin') {
            $this->flash('danger', 'Superadmin role cannot be modified here.');
            $this->redirect('/dashboard/superadmin/users');
        }
        if ($user['role'] === $targetRole) {
            $this->flash('info', 'User already has this role.');
            $this->redirect($this->redirectBack());
        }

        $stmt = $this->pdo()->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute([':role' => $targetRole, ':id' => $user['id']]);
        audit_log($this->actorId(), 'user.role', 'user', (string)$user['id'], ['from' => $user['role'], 'to' => $targetRole]);
        $this->flash('success', sprintf('User %s promoted to %s.', $user['name'], ucfirst($targetRole)));
        $this->redirect($this->redirectBack());
    }

    public function updateStatus($id): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();
        $user = $this->findUser((int)$id);
        if (!$user) {
            http_response_code(404);
            echo 'User not found';
            return;
        }
        $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
        if ($user['role'] === 'superadmin' && !$active) {
            $this->flash('danger', 'Cannot deactivate a superadmin.');
            $this->redirect($this->redirectBack());
        }
        $stmt = $this->pdo()->prepare('UPDATE users SET active = :active WHERE id = :id');
        $stmt->execute([':active' => $active, ':id' => $user['id']]);
        audit_log($this->actorId(), 'user.status', 'user', (string)$user['id'], ['active' => $active]);
        $this->flash('success', sprintf('User %s is now %s.', $user['name'], $active ? 'active' : 'inactive'));
        $this->redirect($this->redirectBack());
    }

    public function bulkRole(): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();
        $targetRole = trim((string)($_POST['role'] ?? ''));
        $allowed = ['user', 'mufti', 'admin'];
        if (!in_array($targetRole, $allowed, true)) {
            $this->flash('danger', 'Role not allowed.');
            $this->redirect($this->redirectBack());
        }
        $idsInput = trim((string)($_POST['identifiers'] ?? ''));
        if ($idsInput === '') {
            $this->flash('danger', 'No users provided.');
            $this->redirect($this->redirectBack());
        }
        $lines = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $idsInput)));
        $pdo = $this->pdo();
        $updated = 0;
        foreach ($lines as $identifier) {
            $user = $this->findUserByIdentifier($identifier);
            if (!$user || $user['role'] === 'superadmin') {
                continue;
            }
            if ($user['role'] === $targetRole) {
                continue;
            }
            $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $stmt->execute([':role' => $targetRole, ':id' => $user['id']]);
            audit_log($this->actorId(), 'user.role.bulk', 'user', (string)$user['id'], ['to' => $targetRole]);
            $updated++;
        }
        if ($updated === 0) {
            $this->flash('info', 'No users updated.');
        } else {
            $this->flash('success', sprintf('%d users moved to %s.', $updated, ucfirst($targetRole)));
        }
        $this->redirect('/dashboard/superadmin/users');
    }

    private function findUser(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT id, name, email, role, active FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function findUserByIdentifier(string $identifier): ?array
    {
        if (ctype_digit($identifier)) {
            return $this->findUser((int)$identifier);
        }
        $stmt = $this->pdo()->prepare('SELECT id, name, email, role, active FROM users WHERE LOWER(email) = :email');
        $stmt->execute([':email' => mb_strtolower($identifier)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function redirectBack(): string
    {
        return $_POST['redirect'] ?? '/dashboard/superadmin/users';
    }
}
