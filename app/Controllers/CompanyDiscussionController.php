<?php
namespace App\Controllers;

use Core\Controller;
use PDO;

class CompanyDiscussionController extends Controller
{
    private function pdo(): PDO {
        $dsn  = $_ENV['DB_DSN']  ?? 'sqlite:/Users/shaikhoology/SM/MAIN-Shaikhoology/ShriahScreenerShaikhoology/storage/shaikhoology.sqlite';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public function index($symbol)
    {
        $pdo = $this->pdo();
        $c = $pdo->prepare("SELECT id, ticker, name FROM companies WHERE ticker=:t LIMIT 1");
        $c->execute([':t'=>$symbol]);
        $company = $c->fetch(PDO::FETCH_ASSOC);

        $posts = [];
        if ($company) {
            $t = $pdo->prepare("SELECT id FROM discussion_threads WHERE company_id=:cid ORDER BY id LIMIT 1");
            $t->execute([':cid'=>$company['id']]);
            $tid = $t->fetchColumn();
            if ($tid) {
                $p = $pdo->prepare("SELECT body_md, created_at FROM discussion_posts WHERE thread_id=:tid AND status='visible' ORDER BY id DESC");
                $p->execute([':tid'=>$tid]);
                $posts = $p->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $this->view('company/discussion', [
            'symbol'=>$symbol,
            'company'=>$company,
            'posts'=>$posts,
            'csrf'=>$_SESSION['csrf'] ?? ''
        ]);
    }

    public function store($symbol)
    {
        if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
            http_response_code(400); echo 'CSRF failed'; return;
        }
        $body = trim((string)($_POST['body'] ?? ''));
        if ($body === '') { header('Location: /company/'.$symbol.'/discussion'); return; }

        $pdo = $this->pdo();
        $c = $pdo->prepare("SELECT id FROM companies WHERE ticker=:t LIMIT 1");
        $c->execute([':t'=>$symbol]);
        $cid = $c->fetchColumn();
        if (!$cid) { http_response_code(404); echo 'Company not found'; return; }

        $t = $pdo->prepare("SELECT id FROM discussion_threads WHERE company_id=:cid ORDER BY id LIMIT 1");
        $t->execute([':cid'=>$cid]);
        $tid = $t->fetchColumn();
        if (!$tid) {
            $pdo->prepare("INSERT INTO discussion_threads (company_id, title) VALUES (?,?)")->execute([$cid, 'General discussion']);
            $tid = (int)$pdo->lastInsertId();
        }

        // basic sanitize: strip tags except a,b,i,code,pre
        $clean = strip_tags($body, '<a><b><i><code><pre>');
        $pdo->prepare("INSERT INTO discussion_posts (thread_id, author_id, body_md) VALUES (?,?,?)")
            ->execute([$tid, 1, $clean]);

        header('Location: /company/'.$symbol.'/discussion');
    }
}
