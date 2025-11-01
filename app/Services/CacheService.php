<?php
namespace App\Services;

class CacheService {
    private $cacheDir;

    public function __construct() {
        $this->cacheDir = app_root() . '/storage/cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key) {
        $file = $this->cacheDir . '/' . md5($key) . '.cache';
        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize(file_get_contents($file));
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = 300) {
        $file = $this->cacheDir . '/' . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        file_put_contents($file, serialize($data));
    }

    public function clear() {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function getCompanyHeaderCacheKey($companyId) {
        // Get latest published CMV ID for cache busting
        $pdo = db_pdo();
        $stmt = $pdo->prepare("SELECT cmv.id FROM compliance_master_current cmc JOIN compliance_master_versions cmv ON cmv.id = cmc.cmv_id_published WHERE cmc.id = 1");
        $stmt->execute();
        $cmvId = $stmt->fetchColumn() ?: 0;

        return "company_header_{$companyId}_{$cmvId}";
    }
}
