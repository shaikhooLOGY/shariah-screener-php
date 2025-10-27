<?php
namespace App\Controllers;

use Core\Controller;
use App\Services\ScreeningEngine;
use App\Middleware\RateLimitGuard;
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

    public function submitRatioSuggestion(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || !isset($user['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        // Rate limiting: 10 requests per minute per IP
        $rateLimit = new RateLimitGuard();
        $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$rateLimit->check("suggest_ratio_{$clientIP}", 10, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $companyId = (int)($input['company_id'] ?? 0);
        $period = trim($input['period'] ?? '');
        $ratios = $input['ratios'] ?? [];
        $evidenceUrl = trim($input['evidence_url'] ?? '');

        if (!$companyId || !$period || empty($ratios)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $pdo = $this->pdo();

        // Verify company exists
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE id = ?");
        $stmt->execute([$companyId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Company not found']);
            return;
        }

        // Insert ratio suggestion
        $stmt = $pdo->prepare("
            INSERT INTO ratio_suggestions (company_id, suggested_by, period, payload_json, source, screener_link, status)
            VALUES (?, ?, ?, ?, 'user', ?, 'pending')
        ");
        $stmt->execute([
            $companyId,
            $user['id'],
            $period,
            json_encode($ratios),
            $evidenceUrl
        ]);

        $suggestionId = $pdo->lastInsertId();

        // Audit log
        if (function_exists('audit_log')) {
            audit_log($user['id'], 'suggestion.create', 'ratio_suggestions', $suggestionId, [
                'company_id' => $companyId,
                'period' => $period,
                'ratio_count' => count($ratios)
            ]);
        }

        echo json_encode(['success' => true, 'suggestion_id' => $suggestionId]);
    }
}
