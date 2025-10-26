<?php
namespace App\Controllers\Superadmin;

use App\Services\ScreeningEngine;
use PDO;
use function percent;
use function audit_log;
use function app_root;

class BucketsController extends BaseController
{
    private const BUCKETS = ['pass', 'watch', 'fail'];

    public function index(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();
        $flash = $this->takeFlash();
        $requested = strtolower($_GET['bucket'] ?? 'pass');
        if (!in_array($requested, self::BUCKETS, true)) {
            $requested = 'pass';
        }

        $companies = $this->loadCompanyBuckets($pdo);
        $counts = ['pass' => 0, 'watch' => 0, 'fail' => 0];
        $filtered = [];
        foreach ($companies as $row) {
            $counts[$row['current_bucket']]++;
            if ($row['current_bucket'] === $requested) {
                $filtered[] = $row;
            }
        }

        $this->view('superadmin/buckets', [
            'title' => 'Superadmin · Buckets',
            'flash' => $flash,
            'bucket' => $requested,
            'counts' => $counts,
            'companies' => $filtered,
            'csrf' => $_SESSION['csrf'] ?? '',
        ]);
    }

    public function move($companyId): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();
        $bucket = strtolower(trim((string)($_POST['bucket'] ?? '')));
        $reason = trim((string)($_POST['reason'] ?? ''));
        $redirect = $_POST['redirect'] ?? '/dashboard/superadmin/buckets';

        if (!in_array($bucket, self::BUCKETS, true)) {
            $this->flash('danger', 'Bucket galat hai.');
            $this->redirect($redirect);
        }
        if ($reason === '') {
            $this->flash('danger', 'Reason likhna zaroori hai.');
            $this->redirect($redirect);
        }

        $pdo = $this->pdo();
        $company = $this->loadCompany((int)$companyId);
        if (!$company) {
            http_response_code(404);
            echo 'Company not found';
            return;
        }

        $this->upsertBucket($pdo, (int)$companyId, $bucket, $reason, $this->actorId());
        audit_log($this->actorId(), 'bucket.move', 'company', (string)$companyId, ['bucket' => $bucket]);
        $this->flash('success', sprintf('Ticker %s "%s" me shift ho gaya.', $company['ticker'], ucfirst($bucket)));
        $this->redirect($redirect);
    }

    public function exportCsv(): void
    {
        $this->requireSuperadmin();
        $bucket = strtolower($_GET['bucket'] ?? 'pass');
        if (!in_array($bucket, self::BUCKETS, true)) {
            $bucket = 'pass';
        }
        $companies = $this->loadCompanyBuckets($this->pdo());
        $filtered = array_filter($companies, fn($row) => $row['current_bucket'] === $bucket);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="buckets_'.$bucket.'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Ticker', 'Name', 'Period', 'Debt/Assets', 'Interest/Revenue', 'Liquid/Assets', 'Non-Shariah/Revenue', 'Bucket', 'Reason']);
        foreach ($filtered as $row) {
            $ratios = $row['ratios'];
            fputcsv($out, [
                $row['ticker'],
                $row['name'],
                $row['period'] ?? '',
                percent($ratios['debt_pct'] ?? 0),
                percent($ratios['interest_pct'] ?? 0),
                percent($ratios['liquid_pct'] ?? 0),
                percent($ratios['nonsh_pct'] ?? 0),
                strtoupper($row['current_bucket']),
                $row['override_reason'] ?? '',
            ]);
        }
        fclose($out);
    }

    private function loadCompanyBuckets(PDO $pdo): array
    {
        $engine = new ScreeningEngine();
        $config = require app_root().'/config/screening.php';
        $caps = $config['caps'] ?? [];

        $sql = "
            SELECT c.id, c.ticker, c.name, c.sector_code,
                   f.period, f.total_assets, f.total_debt, f.cash, f.receivables, f.revenue, f.interest_income, f.non_shariah_income,
                   sb.bucket AS override_bucket, sb.reason AS override_reason, sb.updated_at, sb.updated_by,
                   u.name AS updater_name
            FROM companies c
            LEFT JOIN (
                SELECT f1.* FROM filings f1
                INNER JOIN (
                    SELECT company_id, MAX(period) AS latest_period FROM filings GROUP BY company_id
                ) lf ON lf.company_id = f1.company_id AND lf.latest_period = f1.period
            ) f ON f.company_id = c.id
            LEFT JOIN screening_buckets sb ON sb.company_id = c.id
            LEFT JOIN users u ON u.id = sb.updated_by
            ORDER BY c.ticker
        ";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $filing = [
                'total_assets' => $row['total_assets'],
                'total_debt' => $row['total_debt'],
                'cash' => $row['cash'],
                'receivables' => $row['receivables'],
                'revenue' => $row['revenue'],
                'interest_income' => $row['interest_income'],
                'non_shariah_income' => $row['non_shariah_income'],
            ];
            if ($row['period'] === null) {
                $ratios = ['debt_pct' => 0, 'interest_pct' => 0, 'liquid_pct' => 0, 'nonsh_pct' => 0];
                $derived = 'watch';
                $autoReason = 'Latest filing missing';
            } else {
                $ratios = $engine->compute($filing);
                $derived = $this->determineBucket($ratios, $caps);
                $autoReason = 'Auto verdict';
            }

            $current = $row['override_bucket'] ?: $derived;
            $results[] = [
                'company_id' => (int)$row['id'],
                'ticker' => $row['ticker'],
                'name' => $row['name'],
                'sector' => $row['sector_code'],
                'period' => $row['period'],
                'ratios' => $ratios,
                'derived_bucket' => $derived,
                'current_bucket' => $current,
                'override_bucket' => $row['override_bucket'],
                'override_reason' => $row['override_reason'] ?? $autoReason,
                'updated_at' => $row['updated_at'],
                'updated_by_name' => $row['updater_name'],
            ];
        }
        return $results;
    }

    private function determineBucket(array $ratios, array $caps): string
    {
        $caps = array_merge(['debt' => 0.33, 'interest' => 0.05, 'liquid' => 0.7, 'nonsh' => 0.05], $caps);
        $pass = ($ratios['debt_pct'] ?? INF) <= $caps['debt']
            && ($ratios['interest_pct'] ?? INF) <= $caps['interest']
            && ($ratios['liquid_pct'] ?? INF) <= $caps['liquid']
            && ($ratios['nonsh_pct'] ?? INF) <= $caps['nonsh'];
        if ($pass) {
            return 'pass';
        }
        if (($ratios['nonsh_pct'] ?? 1) > $caps['nonsh']) {
            return 'fail';
        }
        return 'watch';
    }

    private function loadCompany(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT id, ticker, name FROM companies WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function upsertBucket(PDO $pdo, int $companyId, string $bucket, string $reason, ?int $userId): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('INSERT INTO screening_buckets (company_id, bucket, reason, updated_by, updated_at) VALUES (:company_id, :bucket, :reason, :updated_by, CURRENT_TIMESTAMP)
                ON CONFLICT(company_id) DO UPDATE SET bucket = excluded.bucket, reason = excluded.reason, updated_by = excluded.updated_by, updated_at = CURRENT_TIMESTAMP');
            $stmt->execute([
                ':company_id' => $companyId,
                ':bucket' => $bucket,
                ':reason' => $reason,
                ':updated_by' => $userId,
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO screening_buckets (company_id, bucket, reason, updated_by, updated_at) VALUES (:company_id, :bucket, :reason, :updated_by, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE bucket = VALUES(bucket), reason = VALUES(reason), updated_by = VALUES(updated_by), updated_at = CURRENT_TIMESTAMP');
            $stmt->execute([
                ':company_id' => $companyId,
                ':bucket' => $bucket,
                ':reason' => $reason,
                ':updated_by' => $userId,
            ]);
        }
    }
}
