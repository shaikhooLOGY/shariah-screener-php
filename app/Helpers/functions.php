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
    return $_SESSION['role'] ?? 'guest';
}

function role_index(string $role): int {
    $hierarchy = role_hierarchy();
    $index = array_search($role, $hierarchy, true);
    return $index === false ? -1 : $index;
}

function role_at_least(string $roleRequired): bool {
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
