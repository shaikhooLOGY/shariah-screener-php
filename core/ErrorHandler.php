<?php
namespace Core;

class ErrorHandler {
  public static function register(): void {
    set_exception_handler(function($e){
      http_response_code(500);
      error_log($e);
      $isProd = (($_ENV['APP_ENV'] ?? 'production') === 'production');
      if ($isProd) {
        echo '500';
      } else {
        echo '<pre style="white-space:pre-wrap;padding:12px;border:1px solid #ddd;border-radius:6px;background:#fafafa">';
        echo htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
      }
    });
  }
}
