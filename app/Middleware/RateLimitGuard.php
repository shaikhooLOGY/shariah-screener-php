<?php
namespace App\Middleware;

class RateLimitGuard {
    private $storagePath;

    public function __construct() {
        $this->storagePath = app_root() . '/storage/ratelimit';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function check($key, $limit, $windowSeconds) {
        $file = $this->storagePath . '/' . md5($key) . '.json';

        $now = time();
        $data = [];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
            // Clean old entries
            $data = array_filter($data, function($timestamp) use ($now, $windowSeconds) {
                return ($now - $timestamp) < $windowSeconds;
            });
        }

        $count = count($data);

        if ($count >= $limit) {
            return false;
        }

        // Add current request
        $data[] = $now;
        file_put_contents($file, json_encode($data));

        return true;
    }
}