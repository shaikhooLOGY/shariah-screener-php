<?php
namespace App\Controllers\Superadmin;

use App\Services\ScreeningEngine;
use PDO;
use function audit_log;
use function app_root;

class EngineController extends BaseController
{
    public function index(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();

        if (isset($_GET['job']) && isset($_GET['poll'])) {
            $job = $this->fetchJob((int)$_GET['job']);
            if (!$job) {
                http_response_code(404);
                $this->json(['error' => 'Not found'], 404);
                return;
            }
            $this->json($job);
            return;
        }

        $flash = $this->takeFlash();
        $jobs = $this->loadJobs($pdo);
        $activeJobId = isset($_GET['job']) ? (int)$_GET['job'] : null;
        $activeJob = $activeJobId ? $this->fetchJob($activeJobId) : null;

        $this->view('superadmin/engine', [
            'title' => 'Superadmin · Screening engine',
            'jobs' => $jobs,
            'flash' => $flash,
            'activeJobId' => $activeJobId,
            'activeJob' => $activeJob,
            'csrf' => $_SESSION['csrf'] ?? '',
        ]);
    }

    public function run(): void
    {
        $this->requireSuperadmin();
        $this->requirePost();
        $this->assertCsrf();

        $scope = $_POST['scope'] ?? 'ticker';
        $value = trim((string)($_POST['value'] ?? ''));
        $dry = isset($_POST['dry']);
        $notify = isset($_POST['notify']);

        $pdo = $this->pdo();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO engine_runs (scope, value, status, created_by) VALUES (:scope, :value, :status, :created_by)');
        $stmt->execute([
            ':scope' => $scope,
            ':value' => $value,
            ':status' => 'queued',
            ':created_by' => $this->actorId(),
        ]);
        $runId = (int)$pdo->lastInsertId();
        $pdo->commit();

        $startTime = date('Y-m-d H:i:s');
        $pdo->prepare('UPDATE engine_runs SET status = :status, started_at = :started_at WHERE id = :id')
            ->execute([':status' => 'running', ':started_at' => $startTime, ':id' => $runId]);

        $result = $this->executeScope($scope, $value, $dry);
        $status = $result['status'];
        $summary = json_encode($result, JSON_UNESCAPED_UNICODE);
        $finish = date('Y-m-d H:i:s');

        $pdo->prepare('UPDATE engine_runs SET status = :status, finished_at = :finished_at, summary = :summary WHERE id = :id')
            ->execute([':status' => $status, ':finished_at' => $finish, ':summary' => $summary, ':id' => $runId]);

        audit_log($this->actorId(), 'engine.run', 'engine_runs', (string)$runId, ['scope' => $scope, 'value' => $value, 'dry' => $dry, 'status' => $status]);

        if ($notify) {
            $this->flash($status === 'success' ? 'success' : 'danger', $result['message']);
        }

        $this->redirect('/dashboard/superadmin/engine?job='.$runId);
    }

    private function executeScope(string $scope, string $value, bool $dry): array
    {
        $pdo = $this->pdo();
        $engine = new ScreeningEngine();
        $config = require app_root().'/config/screening.php';
        $caps = $config['caps'] ?? [];

        $tickers = [];
        try {
            if ($scope === 'ticker') {
                if ($value === '') {
                    return ['status' => 'failed', 'message' => 'Ticker required'];
                }
                $stmt = $pdo->prepare('SELECT id, ticker FROM companies WHERE UPPER(ticker) = :ticker LIMIT 1');
                $stmt->execute([':ticker' => strtoupper($value)]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    return ['status' => 'failed', 'message' => 'Ticker not found'];
                }
                $tickers[] = $row;
            } elseif ($scope === 'sector') {
                if ($value === '') {
                    return ['status' => 'failed', 'message' => 'Sector required'];
                }
                $stmt = $pdo->prepare('SELECT id, ticker FROM companies WHERE sector_code = :sector');
                $stmt->execute([':sector' => $value]);
                $tickers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!$tickers) {
                    return ['status' => 'failed', 'message' => 'No companies in this sector'];
                }
            } else {
                $stmt = $pdo->query('SELECT id, ticker FROM companies ORDER BY ticker');
                $tickers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!$tickers) {
                    return ['status' => 'failed', 'message' => 'No companies found'];
                }
            }
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => 'Error fetching companies: '.$e->getMessage()];
        }

        $computed = 0;
        $missingFilings = [];
        $ratios = [];
        foreach ($tickers as $company) {
            $filingStmt = $pdo->prepare('SELECT * FROM filings WHERE company_id = :cid ORDER BY period DESC LIMIT 1');
            $filingStmt->execute([':cid' => $company['id']]);
            $filing = $filingStmt->fetch(PDO::FETCH_ASSOC);
            if (!$filing) {
                $missingFilings[] = $company['ticker'];
                continue;
            }
            $result = $engine->compute($filing);
            $verdict = $this->determineVerdict($result, $caps);
            $ratios[] = ['ticker' => $company['ticker'], 'ratios' => $result, 'verdict' => $verdict];
            $computed++;
        }

        $status = $computed > 0 ? 'success' : 'failed';
        $message = $computed.' companies recomputed';
        if ($missingFilings) {
            $message .= '; missing filings: '.implode(', ', $missingFilings);
        }

        return [
            'status' => $status,
            'message' => $message,
            'computed' => $computed,
            'missing' => $missingFilings,
            'ratios' => $dry ? $ratios : [],
        ];
    }

    private function determineVerdict(array $ratios, array $caps): string
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
            return 'watch';
        }
        return 'fail';
    }

    private function fetchJob(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT id, scope, value, status, started_at, finished_at, summary FROM engine_runs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$job) {
            return null;
        }
        $job['summary'] = $job['summary'] ? json_decode($job['summary'], true) : [];
        return $job;
    }

    private function loadJobs(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, scope, value, status, started_at, finished_at, summary FROM engine_runs ORDER BY id DESC LIMIT 20');
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($jobs as &$job) {
            $job['summary'] = $job['summary'] ? json_decode($job['summary'], true) : [];
        }
        return $jobs;
    }

}
