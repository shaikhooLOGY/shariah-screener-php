<?php
namespace Core;

class Response {
    public static function send($content = '', $statusCode = 200, $headers = []) {
        // Add performance timing header
        if (isset($_ENV['REQUEST_START'])) {
            $execTime = round((microtime(true) - $_ENV['REQUEST_START']) * 1000, 2);
            header("X-Exec-Time: {$execTime}ms");

            // Log slow requests
            if ($execTime > 400) {
                app_log('warning', 'Slow request', [
                    'url' => $_SERVER['REQUEST_URI'] ?? '',
                    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                    'time_ms' => $execTime
                ]);
            }
        }

        // Add request ID for tracking
        $requestId = bin2hex(random_bytes(8));
        header("X-Request-Id: {$requestId}");

        http_response_code($statusCode);

        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if (is_array($content) || is_object($content)) {
            header('Content-Type: application/json');
            echo json_encode($content);
        } else {
            echo $content;
        }
    }
}
