<?php
declare(strict_types=1);

$dsn = 'sqlite:' . __DIR__ . '/../storage/shaikhoology.sqlite';
$pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys = ON;');

/* discussions */
$pdo->exec("
CREATE TABLE IF NOT EXISTS discussion_threads (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  company_id INTEGER,
  title TEXT NOT NULL,
  created_by INTEGER,
  locked INTEGER DEFAULT 0,
  created_at TEXT DEFAULT (datetime('now'))
);
");
$pdo->exec("
CREATE TABLE IF NOT EXISTS discussion_posts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  thread_id INTEGER NOT NULL,
  author_id INTEGER,
  role TEXT DEFAULT 'user',
  body_md TEXT NOT NULL,
  status TEXT DEFAULT 'visible',
  created_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY(thread_id) REFERENCES discussion_threads(id) ON DELETE CASCADE
);
");

/* suggestions */
$pdo->exec("
CREATE TABLE IF NOT EXISTS suggestions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  company_id INTEGER NOT NULL,
  period TEXT NOT NULL,
  user_id INTEGER,
  totals_json TEXT NOT NULL,
  evidence_url TEXT,
  note TEXT,
  status TEXT DEFAULT 'pending',
  created_at TEXT DEFAULT (datetime('now'))
);
");

/* seed thread if missing */
$exists = (int)$pdo->query("SELECT COUNT(*) FROM discussion_threads")->fetchColumn();
if ($exists === 0) {
  $cid = (int)$pdo->query("SELECT id FROM companies WHERE ticker='TCS'")->fetchColumn();
  if ($cid) {
    $pdo->prepare("INSERT INTO discussion_threads (company_id, title) VALUES (?,?)")
        ->execute([$cid, 'General discussion']);
    $tid = (int)$pdo->lastInsertId();
    $pdo->prepare("INSERT INTO discussion_posts (thread_id, author_id, body_md) VALUES (?,?,?)")
        ->execute([$tid, 1, 'Welcome! Share sources and reasoning here.']);
  }
}

echo "SQLite discussion+suggestions ready.\n";
