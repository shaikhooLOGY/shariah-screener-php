<?php
namespace Core;
use Dotenv\Dotenv;

class Bootstrap {
  public function init(): void {
    $root = dirname(__DIR__);
    if (file_exists($root.'/.env')) {
      $dotenv = Dotenv::createImmutable($root);
      $dotenv->load();
    }
    date_default_timezone_set($_ENV['APP_TZ'] ?? 'UTC');
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
      if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
      }
    }
  }
}
