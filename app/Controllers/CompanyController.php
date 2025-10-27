<?php
namespace App\Controllers;

use Core\Controller;
use App\Services\ScreeningEngine;
use App\Services\CacheService;
use PDO;
use function resolve_dsn;

class CompanyController extends Controller
{
    private function pdo(): PDO {
        $dsn  = $_ENV['DB_DSN']  ?? 'sqlite:./storage/shaikhoology.sqlite';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $dsn = resolve_dsn($dsn);
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    private function caps(): array {
        $cfgPath = dirname(__DIR__, 2) . '/config/screening.php';
        $cfg = is_file($cfgPath) ? require $cfgPath : ['caps'=>['debt'=>0.25,'interest'=>0.025,'nonsh'=>0.025,'liquid'=>0.90]];
        return $cfg['caps'];
    }

    public function show($symbol)
    {
        $pdo = $this->pdo();
        $cache = new CacheService();

        $c = $pdo->prepare("SELECT id, ticker, name FROM companies WHERE ticker = :t LIMIT 1");
        $c->execute([':t' => $symbol]);
        $company = $c->fetch(PDO::FETCH_ASSOC);

        $filing = null; $ratios = null; $verdict = null; $why = []; $cmvInfo = null; $history = [];
        $sectorInfo = null; $pendingSuggestionsCount = 0; $openControversiesCount = 0;

        // Get current user from session
        $currentUser = $_SESSION['user'] ?? null;

        // Try to get cached company header data
        $cacheKey = $cache->getCompanyHeaderCacheKey($company['id'] ?? 0);
        $cachedHeader = $cache->get($cacheKey);

        if ($cachedHeader && $company) {
            // Use cached data
            $verdict = $cachedHeader['verdict'];
            $cmvInfo = $cachedHeader['cmvInfo'];
        } elseif ($company) {
            // Compute and cache header data
            $headerData = $this->computeCompanyHeader($pdo, $company['id']);
            $cache->set($cacheKey, $headerData, 900); // 15 minutes TTL
            $verdict = $headerData['verdict'];
            $cmvInfo = $headerData['cmvInfo'];
        }

        if ($company) {
            $f = $pdo->prepare("SELECT * FROM filings WHERE company_id = :cid ORDER BY period DESC LIMIT 1");
            $f->execute([':cid' => $company['id']]);
            $filing = $f->fetch(PDO::FETCH_ASSOC);

            // Get sector information
            $stmt = $pdo->prepare("
                SELECT s.name, s.is_compliant, s.rationale
                FROM company_sector_map csm
                JOIN sectors s ON s.id = csm.sector_id
                WHERE csm.company_id = :cid
                LIMIT 1
            ");
            $stmt->execute([':cid' => $company['id']]);
            $sector = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sector) {
                $sectorInfo = [
                    'name' => $sector['name'],
                    'is_compliant' => (bool)$sector['is_compliant'],
                    'rationale' => $sector['rationale']
                ];
            }

            // Get pending suggestions count (if user is logged in)
            if ($currentUser) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM ratio_suggestions WHERE company_id = :cid AND status = 'pending'");
                $stmt->execute([':cid' => $company['id']]);
                $pendingSuggestionsCount = (int)$stmt->fetchColumn();

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM controversy_queue WHERE company_id = :cid AND status = 'open'");
                $stmt->execute([':cid' => $company['id']]);
                $openControversiesCount = (int)$stmt->fetchColumn();
            }

            // Get published CMV result
            $stmt = $pdo->prepare("
                SELECT cmv.label, cmv.period, cr.final_score, cr.verdict, cr.breaches_json
                FROM compliance_master_current cmc
                JOIN compliance_master_versions cmv ON cmv.id = cmc.cmv_id_published
                LEFT JOIN cmv_results cr ON cr.cmv_id = cmv.id AND cr.company_id = :cid
                WHERE cmc.id = 1
            ");
            $stmt->execute([':cid' => $company['id']]);
            $cmvResult = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cmvResult) {
                $cmvInfo = [
                    'label' => $cmvResult['label'],
                    'period' => $cmvResult['period'],
                    'final_score' => $cmvResult['final_score'],
                    'verdict' => $cmvResult['verdict'],
                    'breaches' => json_decode($cmvResult['breaches_json'] ?? '[]', true)
                ];
                $verdict = ucfirst($cmvResult['verdict']);
            } else {
                // No published CMV yet - show graceful empty state
                $verdict = 'No published CMV yet';
                $cmvInfo = null;

                // Still show legacy engine results if filing exists
                if ($filing) {
                    $engine = new ScreeningEngine();
                    $ratios = $engine->compute($filing);

                    $caps = $this->caps();
                    $pass = true;

                    if ($ratios['debt_pct'] > $caps['debt']) { $pass = false; $why[] = 'Debt exceeds cap'; }
                    if ($ratios['interest_pct'] > $caps['interest']) { $pass = false; $why[] = 'Interest income exceeds cap'; }
                    if ($ratios['liquid_pct'] < $caps['liquid']) { $pass = false; $why[] = 'Liquid assets below cap'; }
                    if ($ratios['nonsh_pct'] > $caps['nonsh']) { $pass = false; $why[] = 'Non-Shari\'ah income exceeds cap'; }

                    if ($pass) {
                        $verdict = 'Pass (Legacy - No CMV)';
                    } else {
                        $verdict = 'Fail (Legacy - No CMV)';
                    }
                }
            }

            // Get CMV history (last 3)
            $stmt = $pdo->prepare("
                SELECT cmv.label, cmv.period, cr.verdict, cr.final_score
                FROM compliance_master_versions cmv
                LEFT JOIN cmv_results cr ON cr.cmv_id = cmv.id AND cr.company_id = :cid
                WHERE cmv.status IN ('published', 'archived')
                ORDER BY cmv.created_at DESC
                LIMIT 3
            ");
            $stmt->execute([':cid' => $company['id']]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('company/profile', compact('symbol','company','filing','ratios','verdict','cmvInfo','history','sectorInfo','pendingSuggestionsCount','openControversiesCount') + ['caps'=>$this->caps(), 'why'=>$why]);
    }

    public function profile($symbol)
    {
        $this->show($symbol);
    }

    private function computeCompanyHeader($pdo, $companyId) {
        // Get published CMV result
        $stmt = $pdo->prepare("
            SELECT cmv.label, cmv.period, cr.final_score, cr.verdict, cr.breaches_json
            FROM compliance_master_current cmc
            JOIN compliance_master_versions cmv ON cmv.id = cmc.cmv_id_published
            LEFT JOIN cmv_results cr ON cr.cmv_id = cmv.id AND cr.company_id = :cid
            WHERE cmc.id = 1
        ");
        $stmt->execute([':cid' => $companyId]);
        $cmvResult = $stmt->fetch(PDO::FETCH_ASSOC);

        $verdict = 'No published CMV yet';
        $cmvInfo = null;

        if ($cmvResult) {
            $cmvInfo = [
                'label' => $cmvResult['label'],
                'period' => $cmvResult['period'],
                'final_score' => $cmvResult['final_score'],
                'verdict' => $cmvResult['verdict'],
                'breaches' => json_decode($cmvResult['breaches_json'] ?? '[]', true)
            ];
            $verdict = ucfirst($cmvResult['verdict']);
        }

        return [
            'verdict' => $verdict,
            'cmvInfo' => $cmvInfo
        ];
    }
}
