<?php
namespace Core;
class Controller {
  protected function view(string $template, array $data=[]): void {
    extract($data);
    $path = dirname(__DIR__).'/app/Views/'.$template.'.php';
    if (file_exists($path)) include $path; else echo 'View missing: '.htmlspecialchars($template);
  }
  protected function json($data,int $code=200): void { http_response_code($code); header('Content-Type: application/json'); echo json_encode($data); }
}