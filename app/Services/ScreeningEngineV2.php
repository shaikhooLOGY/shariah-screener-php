<?php
namespace App\Services;

use function db_pdo;
use function config;

class ScreeningEngineV2
{
    private array $caps;
    private array $weights;

    public function __construct()
    {
        $configPath = app_root() . '/config/screening.php';
        $config = is_file($configPath) ? require $configPath : [];
        $this->caps = $config['caps'] ?? [];
        $this->weights = $config['weights'] ?? ['financial' => 0.6, 'activity' => 0.4, 'behaviour' => 0.0];
    }

    public function computeRatios(array $filing): array
    {
        $assets = $filing['total_assets'] ?? 0;
        if ($assets <= 0) return ['error' => 'No assets'];

        return [
            'debt_pct' => ($filing['total_debt'] ?? 0) / $assets,
            'interest_pct' => ($filing['interest_income'] ?? 0) / $assets,
            'nonsh_pct' => ($filing['non_shariah_income'] ?? 0) / $assets,
            'liquid_pct' => ($filing['cash_and_equivalents'] ?? 0) / $assets,
            'raw' => [
                'total_assets' => $assets,
                'total_debt' => $filing['total_debt'] ?? 0,
                'interest_income' => $filing['interest_income'] ?? 0,
                'non_shariah_income' => $filing['non_shariah_income'] ?? 0,
                'cash_and_equivalents' => $filing['cash_and_equivalents'] ?? 0,
            ]
        ];
    }

    public function scoreFinancial(array $ratios, array $sectorCaps = []): array
    {
        $caps = array_merge($this->caps, $sectorCaps);
        $breaches = [];
        $score = 1.0;

        if ($ratios['debt_pct'] > $caps['debt']) {
            $breaches[] = 'debt';
            $score -= 0.3;
        }
        if ($ratios['interest_pct'] > $caps['interest']) {
            $breaches[] = 'interest';
            $score -= 0.3;
        }
        if ($ratios['nonsh_pct'] > $caps['nonsh']) {
            $breaches[] = 'nonsh';
            $score -= 0.3;
        }
        if ($ratios['liquid_pct'] < $caps['liquid']) {
            $breaches[] = 'liquid';
            $score -= 0.1;
        }

        return [
            'score' => max(0.0, $score),
            'breaches' => $breaches,
            'caps_used' => $caps,
            'ratios' => $ratios
        ];
    }

    public function scoreActivity(string $sector, array $companyMeta): array
    {
        // Stub implementation - in real system would analyze business activities
        $score = 0.9; // Assume mostly compliant
        $notes = ["Sector: {$sector} - activity analysis stub"];

        return [
            'score' => $score,
            'notes' => $notes
        ];
    }

    public function scoreBehaviour(int $companyId): array
    {
        // Stub implementation - future use
        return [
            'score' => 1.0,
            'flags' => []
        ];
    }

    public function finalize(array $financial, array $activity, array $behaviour): array
    {
        $financialScore = $financial['score'] * $this->weights['financial'];
        $activityScore = $activity['score'] * $this->weights['activity'];
        $behaviourScore = $behaviour['score'] * $this->weights['behaviour'];

        $finalScore = $financialScore + $activityScore + $behaviourScore;

        $verdict = match(true) {
            $finalScore >= 0.8 => 'compliant',
            $finalScore >= 0.6 => 'grey',
            default => 'noncompliant'
        };

        return [
            'final_score' => $finalScore,
            'verdict' => $verdict,
            'components' => [
                'financial' => $financialScore,
                'activity' => $activityScore,
                'behaviour' => $behaviourScore
            ]
        ];
    }

    public function runForCompany(int $companyId, string $period): ?array
    {
        $pdo = db_pdo();

        // Get latest filing for company and period
        $stmt = $pdo->prepare("SELECT * FROM filings WHERE company_id = ? AND period = ? ORDER BY filing_date DESC LIMIT 1");
        $stmt->execute([$companyId, $period]);
        $filing = $stmt->fetch();

        if (!$filing) return null;

        // Get company info
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();

        if (!$company) return null;

        // Compute ratios
        $ratios = $this->computeRatios($filing);
        if (isset($ratios['error'])) return null;

        // Score components
        $financial = $this->scoreFinancial($ratios);
        $activity = $this->scoreActivity($company['sector'] ?? 'Unknown', $company);
        $behaviour = $this->scoreBehaviour($companyId);

        // Finalize
        $result = $this->finalize($financial, $activity, $behaviour);

        return array_merge($result, [
            'business_activity_json' => json_encode($activity),
            'financial_json' => json_encode($financial),
            'behaviour_json' => json_encode($behaviour),
            'breaches_json' => json_encode($financial['breaches'] ?? [])
        ]);
    }
}