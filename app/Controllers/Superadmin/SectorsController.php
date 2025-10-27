<?php
namespace App\Controllers\Superadmin;

use Core\Controller;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;

class SectorsController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'sector_caps.edit')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get all sectors
        $stmt = $pdo->query("SELECT * FROM sectors ORDER BY name");
        $sectors = $stmt->fetchAll();

        // Get company counts per sector
        $stmt = $pdo->query("
            SELECT s.id, s.name, COUNT(csm.company_id) as company_count
            FROM sectors s
            LEFT JOIN company_sector_map csm ON csm.sector_id = s.id
            GROUP BY s.id, s.name
            ORDER BY s.name
        ");
        $sectorStats = $stmt->fetchAll();

        $this->view('superadmin/sectors/index', [
            'sectors' => $sectors,
            'sector_stats' => $sectorStats
        ]);
    }

    public function updateCompliance(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'sector_caps.edit')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sectorId = (int)($input['sector_id'] ?? 0);
        $isCompliant = (bool)($input['is_compliant'] ?? false);
        $rationale = trim($input['rationale'] ?? '');

        if (!$sectorId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing sector ID']);
            return;
        }

        $pdo = db_pdo();

        // Update sector compliance
        $stmt = $pdo->prepare("UPDATE sectors SET is_compliant = ?, rationale = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$isCompliant ? 1 : 0, $rationale, $user['id'], $sectorId]);

        audit_log($user['id'], 'sector.update_compliance', 'sectors', $sectorId, [
            'is_compliant' => $isCompliant,
            'rationale' => $rationale
        ]);

        echo json_encode(['success' => true]);
    }

    public function bulkMapCompanies(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'sector_caps.edit')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sectorId = (int)($input['sector_id'] ?? 0);
        $companyIds = $input['company_ids'] ?? [];

        if (!$sectorId || empty($companyIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing sector ID or company IDs']);
            return;
        }

        $pdo = db_pdo();

        // Remove existing mappings for these companies
        $placeholders = str_repeat('?,', count($companyIds) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM company_sector_map WHERE company_id IN ({$placeholders})");
        $stmt->execute($companyIds);

        // Add new mappings
        $stmt = $pdo->prepare("INSERT INTO company_sector_map (company_id, sector_id) VALUES (?, ?)");
        foreach ($companyIds as $companyId) {
            try {
                $stmt->execute([$companyId, $sectorId]);
            } catch (\PDOException $e) {
                // Ignore duplicates
            }
        }

        audit_log($user['id'], 'sector.bulk_map', 'company_sector_map', 0, [
            'sector_id' => $sectorId,
            'company_count' => count($companyIds)
        ]);

        echo json_encode(['success' => true, 'mapped_count' => count($companyIds)]);
    }
}