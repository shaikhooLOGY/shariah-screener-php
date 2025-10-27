<?php
namespace App\Controllers\Superadmin;

use Core\Controller;
use App\Services\ScreeningEngineV2;
use function db_pdo;
use function current_user;
use function user_can;
use function audit_log;
use function set_flash;

class CmvController extends Controller
{
    private ScreeningEngineV2 $engine;

    public function __construct()
    {
        $this->engine = new ScreeningEngineV2();
    }

    public function index(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'cmv.view_diff')) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $pdo = db_pdo();

        // Get current pointers
        $stmt = $pdo->query("SELECT * FROM compliance_master_current WHERE id = 1");
        $current = $stmt->fetch() ?: ['cmv_id_published' => null, 'cmv_id_pending' => null];

        // Get all CMV versions grouped by status
        $stmt = $pdo->query("SELECT * FROM compliance_master_versions ORDER BY created_at DESC");
        $cmvs = $stmt->fetchAll();

        $grouped = ['draft' => [], 'published' => [], 'archived' => [], 'rolled_back' => []];
        foreach ($cmvs as $cmv) {
            $grouped[$cmv['status']][] = $cmv;
        }

        $this->view('superadmin/cmv/index', [
            'grouped_cmvs' => $grouped,
            'current' => $current
        ]);
    }

    public function run(): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'cmv.run')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $scope = $input['scope'] ?? 'all';
        $symbol = $input['symbol'] ?? null;
        $dry = $input['dry'] ?? false;

        try {
            $result = $this->runCmv($scope, $symbol, $dry, $user['id']);

            audit_log($user['id'], 'cmv.run', 'cmv', $result['cmv_id'] ?? null, [
                'scope' => $scope,
                'symbol' => $symbol,
                'dry' => $dry,
                'companies_processed' => $result['companies_processed'] ?? 0
            ]);

            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function diff(int $cmvId): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'cmv.view_diff')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $pdo = db_pdo();
        $stmt = $pdo->prepare("SELECT stats_json FROM cmv_diffs WHERE cmv_id = ?");
        $stmt->execute([$cmvId]);
        $diff = $stmt->fetch();

        if (!$diff) {
            http_response_code(404);
            echo json_encode(['error' => 'Diff not found']);
            return;
        }

        echo json_encode(json_decode($diff['stats_json'], true));
    }

    public function publish(int $cmvId): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'cmv.publish')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $pdo = db_pdo();

        // Verify CMV exists and is draft
        $stmt = $pdo->prepare("SELECT * FROM compliance_master_versions WHERE id = ? AND status = 'draft'");
        $stmt->execute([$cmvId]);
        $cmv = $stmt->fetch();

        if (!$cmv) {
            http_response_code(404);
            echo json_encode(['error' => 'Draft CMV not found']);
            return;
        }

        // Create approval request
        $stmt = $pdo->prepare('INSERT INTO approvals (kind, entity, entity_id, requested_by, payload_json, note) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            'cmv.publish',
            'compliance_master_versions',
            $cmvId,
            $user['id'],
            json_encode(['cmv' => $cmv]),
            "Publish CMV: {$cmv['label']}"
        ]);

        $approvalId = $pdo->lastInsertId();

        audit_log($user['id'], 'approval.create', 'approvals', $approvalId, [
            'kind' => 'cmv.publish',
            'cmv_id' => $cmvId
        ]);

        echo json_encode(['approval_id' => $approvalId, 'status' => 'pending']);
    }

    public function rollback(int $cmvId): void
    {
        $user = current_user();
        if (!$user || !user_can($user, 'cmv.rollback')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $pdo = db_pdo();

        // Verify CMV exists and is published
        $stmt = $pdo->prepare("SELECT * FROM compliance_master_versions WHERE id = ? AND status = 'published'");
        $stmt->execute([$cmvId]);
        $cmv = $stmt->fetch();

        if (!$cmv) {
            http_response_code(404);
            echo json_encode(['error' => 'Published CMV not found']);
            return;
        }

        // Create approval request
        $stmt = $pdo->prepare('INSERT INTO approvals (kind, entity, entity_id, requested_by, payload_json, note) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            'cmv.rollback',
            'compliance_master_versions',
            $cmvId,
            $user['id'],
            json_encode(['cmv' => $cmv]),
            "Rollback CMV: {$cmv['label']}"
        ]);

        $approvalId = $pdo->lastInsertId();

        audit_log($user['id'], 'approval.create', 'approvals', $approvalId, [
            'kind' => 'cmv.rollback',
            'cmv_id' => $cmvId
        ]);

        echo json_encode(['approval_id' => $approvalId, 'status' => 'pending']);
    }

    private function runCmv(string $scope, ?string $symbol, bool $dry, int $userId): array
    {
        $pdo = db_pdo();

        // Determine companies to process
        $companies = [];
        if ($scope === 'ticker' && $symbol) {
            $stmt = $pdo->prepare("SELECT id, ticker FROM companies WHERE ticker = ?");
            $stmt->execute([$symbol]);
            $companies = $stmt->fetchAll();
        } elseif ($scope === 'sector' && $symbol) {
            $stmt = $pdo->prepare("SELECT id, ticker FROM companies WHERE sector = ?");
            $stmt->execute([$symbol]);
            $companies = $stmt->fetchAll();
        } else {
            $stmt = $pdo->query("SELECT id, ticker FROM companies");
            $companies = $stmt->fetchAll();
        }

        if (empty($companies)) {
            return ['error' => 'No companies found for scope'];
        }

        if ($dry) {
            // Dry run - just count and simulate
            $stats = ['companies_found' => count($companies), 'would_process' => count($companies)];
            return ['dry_run' => true, 'stats' => $stats];
        }

        // Create new CMV version
        $period = date('Y-Q') . (ceil(date('n') / 3));
        $label = "CMV {$period} Draft";

        $stmt = $pdo->prepare("INSERT INTO compliance_master_versions (label, period, status, note, created_by) VALUES (?, ?, 'draft', ?, ?)");
        $stmt->execute([$label, $period, "Auto-generated draft for {$scope}", $userId]);
        $cmvId = $pdo->lastInsertId();

        // Process each company
        $results = [];
        $stats = ['compliant' => 0, 'grey' => 0, 'noncompliant' => 0, 'moved' => []];

        foreach ($companies as $company) {
            $result = $this->engine->runForCompany($company['id'], $period);

            if ($result) {
                $stmt = $pdo->prepare("INSERT INTO cmv_results (cmv_id, company_id, period, business_activity_json, financial_json, behaviour_json, final_score, verdict, breaches_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $cmvId,
                    $company['id'],
                    $period,
                    $result['business_activity_json'],
                    $result['financial_json'],
                    $result['behaviour_json'],
                    $result['final_score'],
                    $result['verdict'],
                    $result['breaches_json']
                ]);

                $results[] = $result;
                $stats[$result['verdict']]++;
            }
        }

        // Generate diff stats (simplified)
        $diffStats = [
            'moved' => $stats, // Simplified - in real system compare with previous
            'by_sector' => [], // Would compute sector breakdowns
            'counts' => $stats
        ];

        $stmt = $pdo->prepare("INSERT INTO cmv_diffs (cmv_id, stats_json) VALUES (?, ?)");
        $stmt->execute([$cmvId, json_encode($diffStats)]);

        return [
            'cmv_id' => $cmvId,
            'label' => $label,
            'companies_processed' => count($results),
            'stats' => $diffStats
        ];
    }
}