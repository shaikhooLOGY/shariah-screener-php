<?php
namespace App\Controllers;
use Core\Controller;
use PDO;

class HealthController extends Controller {
  public function index() {
    try {
      $dsn  = $_ENV['DB_DSN']  ?? '';
      $user = $_ENV['DB_USER'] ?? '';
      $pass = $_ENV['DB_PASS'] ?? '';
      $ok = 'no-dsn';
      if ($dsn) {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT 1");
        $ok = $stmt && $stmt->fetchColumn() == 1 ? 'ok' : 'db-fail';
      }
      header('Content-Type: application/json');
      echo json_encode(['status'=>'up','db'=>$ok,'env'=>($_ENV['APP_ENV']??'')], JSON_PRETTY_PRINT);
    } catch (\Throwable $e) {
      http_response_code(500);
      echo json_encode(['status'=>'down','error'=>$e->getMessage()]);
    }
  }
}
