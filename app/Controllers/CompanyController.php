<?php
namespace App\Controllers;

use Core\Controller;
use App\Services\ScreeningEngine;
use PDO;

class CompanyController extends Controller
{
    private function pdo(): PDO {
        $dsn  = $_ENV['DB_DSN']  ?? 'sqlite:./storage/shaikhoology.sqlite';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    private function caps(): array {
        $cfgPath = dirname(__DIR__, 2) . '/config/screening.php';
        $cfg = is_file($cfgPath) ? require $cfgPath : ['caps'=>['debt'=>0.25,'interest'=>0.025,'nonsh'=>0.025,'liquid'=>0.90]];
        return $cfg['caps'];
    }

    public function profile($symbol)
    {
        $pdo = $this->pdo();

        $c = $pdo->prepare("SELECT id, ticker, name FROM companies WHERE ticker = :t LIMIT 1");
        $c->execute([':t' => $symbol]);
        $company = $c->fetch(PDO::FETCH_ASSOC);

        $filing = null; $ratios = null; $verdict = null; $why = [];

        if ($company) {
            $f = $pdo->prepare("SELECT * FROM filings WHERE company_id = :cid ORDER BY period DESC LIMIT 1");
            $f->execute([':cid' => $company['id']]);
            $filing = $f->fetch(PDO::FETCH_ASSOC);

            if ($filing) {
                $engine = new ScreeningEngine();
                $ratios = $engine->compute($filing);

                $caps = $this->caps();
                $pass = true;

                if ($ratios['debt_pct'] > $caps['debt']) { $pass = false; $why[] = 'Debt exceeds cap'; }
                if ($ratios['interest_pct'] > $caps['interest']) { $pass = false; $why[] = 'Interest income exceeds cap'; }
                if ($ratios['liquid_pct'] > $caps['liquid']) { $pass = false; $why[] = 'Liquid assets exceed cap'; }
                if ($ratios['nonsh_pct'] > $caps['nonsh']) { $pass = false; $why[] = 'Non-Shari\'ah income exceeds cap'; }

                $verdict = $pass ? 'Pass' : 'Fail';
            }
        }

        $this->view('company/profile', compact('symbol','company','filing','ratios','verdict') + ['caps'=>$this->caps(), 'why'=>$why]);
    }
}
