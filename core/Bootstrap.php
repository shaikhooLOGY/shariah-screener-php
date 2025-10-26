<?php
namespace Core;

class Bootstrap {
    public static function init(string $root): void {
        // Composer autoload
        require $root . '/vendor/autoload.php';

        // Load .env (simple parser, skip comments/blanks)
        $envFile = $root.'/.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $t = trim($line);
                if ($t === '' || $t[0] === '#') continue;
                if (!str_contains($t, '=')) continue;
                [$k,$v] = array_map('trim', explode('=', $t, 2));
                $_ENV[$k] = $v;
            }
        }

        date_default_timezone_set($_ENV['APP_TZ'] ?? 'UTC');

        // Sessions
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
            $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
        }

        ErrorHandler::register();
    }
}
