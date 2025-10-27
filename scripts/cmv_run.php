<?php
declare(strict_types=1);

/**
 * CLI script to run CMV for companies
 * Usage: php scripts/cmv_run.php --scope=all --dry=0
 *        php scripts/cmv_run.php --scope=ticker --symbol=TCS --dry=1
 *        php scripts/cmv_run.php --scope=sector --symbol=IT --dry=0
 */

require_once __DIR__ . '/../core/Bootstrap.php';

use App\Services\ScreeningEngineV2;

$options = getopt('', ['scope:', 'symbol:', 'dry::']);
$scope = $options['scope'] ?? 'all';
$symbol = $options['symbol'] ?? null;
$dry = isset($options['dry']) ? (bool)$options['dry'] : false;

if (!in_array($scope, ['all', 'ticker', 'sector'])) {
    fwrite(STDERR, "Invalid scope. Use: all, ticker, or sector\n");
    exit(1);
}

if (($scope === 'ticker' || $scope === 'sector') && !$symbol) {
    fwrite(STDERR, "Symbol required for ticker/sector scope\n");
    exit(1);
}

try {
    $pdo = db_pdo();
    $engine = new ScreeningEngineV2();

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
        echo "No companies found for scope: {$scope}" . ($symbol ? " ({$symbol})" : "") . "\n";
        exit(0);
    }

    if ($dry) {
        echo "DRY RUN: Would process " . count($companies) . " companies\n";
        foreach ($companies as $company) {
            echo "  - {$company['ticker']}\n";
        }
        exit(0);
    }

    // Create CMV version
    $period = date('Y') . '-Q' . ceil(date('n') / 3);
    $label = "CMV {$period} CLI Run";

    $stmt = $pdo->prepare("INSERT INTO compliance_master_versions (label, period, status, note, created_by) VALUES (?, ?, 'draft', ?, ?)");
    $stmt->execute([$label, $period, "CLI run for {$scope}", 1]); // created_by = 1 (system)
    $cmvId = $pdo->lastInsertId();

    echo "Created CMV ID: {$cmvId}\n";

    // Process companies
    $stats = ['compliant' => 0, 'grey' => 0, 'noncompliant' => 0, 'processed' => 0];
    $processed = 0;

    foreach ($companies as $company) {
        echo "Processing {$company['ticker']}... ";

        $result = $engine->runForCompany($company['id'], $period);

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

            $stats[$result['verdict']]++;
            $processed++;
            echo "{$result['verdict']} ({$result['final_score']})\n";
        } else {
            echo "skipped (no data)\n";
        }
    }

    // Generate diff stats
    $diffStats = [
        'moved' => $stats,
        'by_sector' => [], // Would compute sector breakdowns
        'counts' => $stats
    ];

    $stmt = $pdo->prepare("INSERT INTO cmv_diffs (cmv_id, stats_json) VALUES (?, ?)");
    $stmt->execute([$cmvId, json_encode($diffStats)]);

    echo "\nCMV Run Complete:\n";
    echo "  Processed: {$processed} companies\n";
    echo "  Compliant: {$stats['compliant']}\n";
    echo "  Grey: {$stats['grey']}\n";
    echo "  Noncompliant: {$stats['noncompliant']}\n";

} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}