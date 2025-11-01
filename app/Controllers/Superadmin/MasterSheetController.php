<?php
namespace App\Controllers\Superadmin;

use PDO;
use function audit_log;

class MasterSheetController extends BaseController
{
    public function index(): void
    {
        $this->requireSuperadmin();
        $this->redirect('/dashboard/superadmin/master-sheet');
    }

    public function masterSheet(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();
        $flash = $this->takeFlash();

        // Get filters from query params
        $filters = [
            'company' => trim((string)($_GET['company'] ?? '')),
            'period' => trim((string)($_GET['period'] ?? '')),
            'source' => trim((string)($_GET['source'] ?? '')),
            'changed_since' => trim((string)($_GET['changed_since'] ?? '')),
            'key' => trim((string)($_GET['key'] ?? '')),
        ];

        // Build query with filters
        $where = ['fs.deleted_at IS NULL'];
        $params = [];

        if ($filters['company']) {
            $where[] = 'c.ticker LIKE :company';
            $params[':company'] = '%' . $filters['company'] . '%';
        }

        if ($filters['period']) {
            $where[] = 'fs.period = :period';
            $params[':period'] = $filters['period'];
        }

        if ($filters['source']) {
            $where[] = 'fs.source = :source';
            $params[':source'] = $filters['source'];
        }

        if ($filters['key']) {
            $where[] = 'fs.key LIKE :key';
            $params[':key'] = '%' . $filters['key'] . '%';
        }

        if ($filters['changed_since']) {
            $where[] = 'fs.updated_at >= :changed_since';
            $params[':changed_since'] = $filters['changed_since'];
        }

        $whereClause = implode(' AND ', $where);

        // Get paginated results
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $countQuery = "SELECT COUNT(*) FROM financial_series fs
                      JOIN companies c ON c.id = fs.company_id
                      WHERE {$whereClause}";
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        $query = "SELECT fs.id, fs.company_id, c.ticker, c.name, fs.period, fs.key, fs.value,
                         fs.source, fs.confidence, fs.provenance_json, fs.updated_at,
                         u.name AS by_user_name, fs.by_user
                  FROM financial_series fs
                  JOIN companies c ON c.id = fs.company_id
                  LEFT JOIN users u ON u.id = fs.by_user
                  WHERE {$whereClause}
                  ORDER BY fs.updated_at DESC
                  LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $ratios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get activity stats for the rail
        $activityStats = $this->getActivityStats($pdo);

        // Get filter options
        $filterOptions = $this->getFilterOptions($pdo);

        $this->view('superadmin/master-sheet', [
            'title' => 'Superadmin · Master Sheet',
            'flash' => $flash,
            'ratios' => $ratios,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
            'activityStats' => $activityStats,
            'csrf' => $_SESSION['csrf'] ?? '',
        ]);
    }

    public function editRatio($id): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();

        $pdo = $this->pdo();
        $value = trim((string)($_POST['value'] ?? ''));
        $confidence = trim((string)($_POST['confidence'] ?? 'med'));
        $note = trim((string)($_POST['note'] ?? ''));

        // Validate confidence
        if (!in_array($confidence, ['low', 'med', 'high'])) {
            $confidence = 'med';
        }

        // Get current ratio
        $stmt = $pdo->prepare('SELECT * FROM financial_series WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            $this->flash('danger', 'Ratio not found.');
            $this->redirect('/dashboard/superadmin/master-sheet');
            return;
        }

        // Update ratio
        $stmt = $pdo->prepare('UPDATE financial_series SET value = ?, confidence = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$value, $confidence, $id]);

        // Log to history
        $stmt = $pdo->prepare('INSERT INTO ratio_history
            (financial_series_id, company_id, period, key, old_value, new_value, change_note, actor_user_id, action, provenance_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $stmt->execute([
            $current['id'],
            $current['company_id'],
            $current['period'],
            $current['key'],
            $current['value'],
            $value,
            $note,
            $this->actorId(),
            'edit',
            $current['provenance_json']
        ]);

        audit_log($this->actorId(), 'ratio.edit', 'financial_series', $id, [
            'old_value' => $current['value'],
            'new_value' => $value,
            'confidence' => $confidence,
            'note' => $note
        ]);

        $this->flash('success', 'Ratio updated successfully.');
        $this->redirect('/dashboard/superadmin/master-sheet');
    }

    public function deleteRatio($id): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();

        $pdo = $this->pdo();
        $note = trim((string)($_POST['note'] ?? ''));

        // Get current ratio
        $stmt = $pdo->prepare('SELECT * FROM financial_series WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            $this->flash('danger', 'Ratio not found.');
            $this->redirect('/dashboard/superadmin/master-sheet');
            return;
        }

        // Soft delete
        $stmt = $pdo->prepare('UPDATE financial_series SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$id]);

        // Log to history
        $stmt = $pdo->prepare('INSERT INTO ratio_history
            (financial_series_id, company_id, period, key, old_value, new_value, change_note, actor_user_id, action, provenance_json)
            VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?)');

        $stmt->execute([
            $current['id'],
            $current['company_id'],
            $current['period'],
            $current['key'],
            $current['value'],
            $note,
            $this->actorId(),
            'delete',
            $current['provenance_json']
        ]);

        audit_log($this->actorId(), 'ratio.delete', 'financial_series', $id, [
            'value' => $current['value'],
            'note' => $note
        ]);

        $this->flash('success', 'Ratio marked for deletion.');
        $this->redirect('/dashboard/superadmin/master-sheet');
    }

    private function getActivityStats(PDO $pdo): array
    {
        // Get recent activity counts
        $stmt = $pdo->query("SELECT
            COUNT(CASE WHEN action = 'edit' AND created_at >= datetime('now', '-24 hours') THEN 1 END) as edits_24h,
            COUNT(CASE WHEN action = 'delete' AND created_at >= datetime('now', '-24 hours') THEN 1 END) as deletes_24h,
            COUNT(CASE WHEN action = 'import' AND created_at >= datetime('now', '-24 hours') THEN 1 END) as imports_24h,
            COUNT(CASE WHEN action = 'engine' AND created_at >= datetime('now', '-24 hours') THEN 1 END) as engines_24h
            FROM ratio_history");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getFilterOptions(PDO $pdo): array
    {
        // Get distinct periods
        $periods = $pdo->query('SELECT DISTINCT period FROM financial_series ORDER BY period DESC')->fetchAll(PDO::FETCH_COLUMN);

        // Get distinct sources
        $sources = $pdo->query('SELECT DISTINCT source FROM financial_series ORDER BY source')->fetchAll(PDO::FETCH_COLUMN);

        // Get distinct keys
        $keys = $pdo->query('SELECT DISTINCT key FROM financial_series ORDER BY key')->fetchAll(PDO::FETCH_COLUMN);

        return [
            'periods' => $periods,
            'sources' => $sources,
            'keys' => $keys,
        ];
    }
}