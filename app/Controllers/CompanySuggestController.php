<?php
namespace App\Controllers;

use Core\Controller;
use App\Services\ScreeningEngine;
use PDO;

class CompanySuggestController extends Controller
{
    private function pdo(): PDO {
        $dsn  = $_ENV['DB_DSN']  ?? 'sqlite:./storage/shaikhoology.sqlite';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public function form($symbol)
    {
        $this->view('company/suggest', ['symbol'=>$symbol, 'csrf'=>$_SESSION['csrf'] ?? '']);
    }

    public function submit($symbol)
    {
        if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
            http_response_code(400); echo 'CSRF failed'; return;
        }

        $period   = trim((string)($_POST['period'] ?? '2025-Q2'));
        $assets   = (float)($_POST['total_assets'] ?? 0);
        $debt     = (float)($_POST['total_debt'] ?? 0);
        $cash     = (float)($_POST['cash'] ?? 0);
        $recv     = (float)($_POST['receivables'] ?? 0);
        $revenue  = (float)($_POST['revenue'] ?? 0);
        $intInc   = (float)($_POST['interest_income'] ?? 0);
        $nonshInc = (float)($_POST['non_shariah_income'] ?? 0);
        $evidence = substr(trim((string)($_POST['evidence_url'] ?? '')), 0, 400);
        $note     = substr(trim((string)($_POST['note'] ?? '')), 0, 2000);

        $totals = [
            'total_assets'=>$assets,'total_debt'=>$debt,'cash'=>$cash,'receivables'=>$recv,
            'revenue'=>$revenue,'interest_income'=>$intInc,'non_shariah_income'=>$nonshInc
        ];
        $totalsJson = json_encode($totals, JSON_UNESCAPED_UNICODE);

        $pdo = $this->pdo();
        $c = $pdo->prepare("SELECT id FROM companies WHERE ticker=:t LIMIT 1");
        $c->execute([':t'=>$symbol]);
        $cid = $c->fetchColumn();
        if (!$cid) { http_response_code(404); echo 'Company not found'; return; }

        $stmt = $pdo->prepare("INSERT INTO suggestions (company_id, period, user_id, totals_json, evidence_url, note, status) VALUES (?,?,?,?,?,?, 'pending')");
        $stmt->execute([$cid, $period, 1, $totalsJson, $evidence, $note]);

        header('Location: /company/'.$symbol.'/suggest?ok=1');
    }
}
