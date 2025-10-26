<?php
namespace Core;
use PDO;
class Model {
  protected static function db(): PDO {
    static $pdo=null;
    if(!$pdo){
      $dsn = $_ENV['DB_DSN'] ?? 'mysql:host=localhost;port=3306;dbname=shaikhoology';
      $user= $_ENV['DB_USER'] ?? 'root';
      $pass= $_ENV['DB_PASS'] ?? '';
      $pdo = new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    }
    return $pdo;
  }
}