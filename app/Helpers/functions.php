<?php

function app_root(): string {
    return $_ENV['APP_ROOT'] ?? dirname(__DIR__, 2);
}

function resolve_dsn(string $dsn): string {
    if (str_starts_with($dsn, 'sqlite:')) {
        $path = substr($dsn, 7);
        if (str_starts_with($path, './')) {
            $path = app_root() . '/' . ltrim($path, './');
        }
        return 'sqlite:' . $path;
    }
    return $dsn;
}

function db_pdo(): \PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = resolve_dsn($_ENV['DB_DSN'] ?? 'sqlite:./storage/shaikhoology.sqlite');
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $pdo = new \PDO($dsn, $user ?: null, $pass ?: null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }
    return $pdo;
}

function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function auth_role(): string {
    return $_SESSION['role'] ?? 'guest';
}

function abilities_config(): array {
    static $config = null;
    if ($config === null) {
        $path = app_root() . '/config/abilities.php';
        if (is_file($path)) {
            $config = require $path;
        } else {
            $config = [
                'hierarchy' => ['guest', 'user', 'mufti', 'admin', 'superadmin'],
                'abilities' => [
                    'guest' => ['read.public'],
                ],
            ];
        }
    }
    return $config;
}

function role_hierarchy(): array {
    $config = abilities_config();
    return $config['hierarchy'] ?? ['guest', 'user', 'mufti', 'admin', 'superadmin'];
}

function current_role(): string {
    return auth_role();
}

function role_index(string $role): int {
    $hierarchy = role_hierarchy();
    $index = array_search($role, $hierarchy, true);
    return $index === false ? -1 : $index;
}

function role_at_least_current(string $roleRequired): bool {
    $currentIndex = role_index(current_role());
    $requiredIndex = role_index($roleRequired);
    return $currentIndex >= 0 && $requiredIndex >= 0 && $currentIndex >= $requiredIndex;
}

function role_abilities(string $role): array {
    $config = abilities_config();
    $hierarchy = role_hierarchy();
    $abilities = [];
    foreach ($hierarchy as $candidate) {
        $abilities = array_merge($abilities, $config['abilities'][$candidate] ?? []);
        if ($candidate === $role) {
            break;
        }
    }
    if (in_array('*', $abilities, true)) {
        return ['*'];
    }
    return array_values(array_unique($abilities));
}

function can(string $ability): bool {
    $abilities = role_abilities(current_role());
    return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
}

function current_user(): ?array {
    return auth_user();
}

function has_role(?array $user, string $role): bool {
    if (!$user) return false;
    return $user['role'] === $role;
}

function role_at_least(?array $user, string $minRole): bool {
    if (!is_array($user) || !isset($user['role'])) return false;
    $order = ['user' => 1, 'mufti' => 2, 'admin' => 3, 'superadmin' => 4];
    $currentLevel = $order[$user['role']] ?? 0;
    $requiredLevel = $order[$minRole] ?? 0;
    return $currentLevel >= $requiredLevel;
}

function user_can(?array $user, string $abilityKey): bool {
    if (!$user || !isset($user['role'])) return false;

    // Check user-specific abilities first (overrides)
    $pdo = db_pdo();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_abilities WHERE user_id = ? AND ability_key = ?');
    $stmt->execute([$user['id'], $abilityKey]);
    if ($stmt->fetchColumn() > 0) {
        return true;
    }

    // Check role abilities
    $roleAbilities = role_abilities($user['role']);
    return in_array('*', $roleAbilities, true) || in_array($abilityKey, $roleAbilities, true);
}

function guard(array $opts): void {
    $user = current_user();
    $minRole = $opts['minRole'] ?? null;
    $abilities = $opts['abilities'] ?? [];

    if ($minRole && !role_at_least($user, $minRole)) {
        if ($user) {
            http_response_code(403);
            echo "Forbidden: Insufficient role";
            exit;
        } else {
            header('Location: /login');
            exit;
        }
    }

    foreach ($abilities as $ability) {
        if (!user_can($user, $ability)) {
            if ($user) {
                http_response_code(403);
                echo "Forbidden: Missing ability '{$ability}'";
                exit;
            } else {
                header('Location: /login');
                exit;
            }
        }
    }
}

function set_flash(string $tone, string $message): void {
    $_SESSION['flash'][$tone] = $message;
}

function take_flash(): array {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect_for_role(string $role): string {
    return match ($role) {
        'superadmin' => '/dashboard/superadmin/system',
        'admin' => '/dashboard/admin',
        'mufti' => '/dashboard/ulama',
        'user' => '/',
        default => '/',
    };
}

function audit_log(?int $actorId, string $action, string $entity, string $entityId, array $meta = []): void {
    try {
        $pdo = db_pdo();
        $stmt = $pdo->prepare('INSERT INTO audit_log (actor_id, action, entity, entity_id, meta) VALUES (:actor_id, :action, :entity, :entity_id, :meta)');
        $stmt->execute([
            ':actor_id' => $actorId,
            ':action' => $action,
            ':entity' => $entity,
            ':entity_id' => (string)$entityId,
            ':meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);
    } catch (\Throwable $e) {
        error_log('audit_log error: '.$e->getMessage());
    }
}

function percent($n){ return number_format($n*100,2).'%' ;}
function periodLabel($p){ return $p; }
