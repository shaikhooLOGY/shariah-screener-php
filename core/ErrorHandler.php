<?php
namespace Core;

class ErrorHandler {
    public static function register(): void {
        set_exception_handler(function(\Throwable $e){
            $prod = (($_ENV['APP_ENV'] ?? 'production') === 'production');
            http_response_code(500);
            if ($prod) {
                echo '500';
            } else {
                echo "<pre>EXCEPTION: ".$e->getMessage()."\n\n".$e->getTraceAsString()."</pre>";
            }
        });
    }
}
