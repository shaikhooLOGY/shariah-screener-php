<?php
namespace App\Controllers;

use PDO;
use function resolve_dsn;

class HealthController {
    public function index() {
        $out = ['status'=>'up','env'=>($_ENV['APP_ENV'] ?? 'unknown')];

        try {
            $dsn  = $_ENV['DB_DSN']  ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';
            if ($dsn) {
                $dsn = resolve_dsn($dsn);
                $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
                $pdo->query('SELECT 1');
                $out['db'] = 'ok';
            } else {
                $out['db'] = 'no-dsn';
            }
        } catch (\Throwable $e) {
            $out['db'] = 'fail';
            $out['error'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($out);
    }
}
