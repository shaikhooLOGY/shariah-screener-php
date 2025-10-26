<?php
declare(strict_types=1);

$dsn = 'sqlite:' . __DIR__ . '/../storage/shaikhoology.sqlite';
$pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$pdo->exec('PRAGMA foreign_keys = ON;');

$pdo->exec("
CREATE TABLE IF NOT EXISTS companies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  isin TEXT,
  ticker TEXT UNIQUE,
  name TEXT NOT NULL,
  sector_code TEXT,
  country TEXT DEFAULT 'India',
  description TEXT
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS filings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  company_id INTEGER NOT NULL,
  period TEXT NOT NULL,
  filing_date TEXT,
  total_assets REAL,
  total_debt REAL,
  cash REAL,
  receivables REAL,
  revenue REAL,
  interest_income REAL,
  non_shariah_income REAL,
  notes_json TEXT,
FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'user',
  active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS feature_flags (
  key TEXT PRIMARY KEY,
  value INTEGER NOT NULL DEFAULT 0,
  label TEXT NOT NULL,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS audit_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  actor_id INTEGER,
  action TEXT,
  entity TEXT,
  entity_id TEXT,
  meta TEXT,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS engine_runs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  scope TEXT,
  value TEXT,
  status TEXT,
  started_at TEXT,
  finished_at TEXT,
  created_by INTEGER,
  summary TEXT
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS screening_buckets (
  company_id INTEGER PRIMARY KEY,
  bucket TEXT NOT NULL,
  reason TEXT,
  updated_by INTEGER,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
);
");

$pdo->exec("
INSERT OR IGNORE INTO companies (isin, ticker, name, sector_code, country, description)
VALUES ('INE467B01029','TCS','Tata Consultancy Services','IT','India','Seed row for local demo');
");

$pdo->exec("
INSERT INTO filings (
  company_id, period, filing_date,
  total_assets, total_debt, cash, receivables, revenue, interest_income, non_shariah_income, notes_json
)
SELECT id, '2025-Q2', '2025-09-30',
       1000.0, 250.0, 300.0, 200.0, 100.0, 2.0, 3.0, '{\"source\":\"seed\"}'
FROM companies WHERE ticker='TCS'
AND NOT EXISTS (SELECT 1 FROM filings f WHERE f.company_id = companies.id AND f.period='2025-Q2');
");

$hash = password_hash('secret', PASSWORD_DEFAULT);
$users = [
    ['Shaikh Super', 'super@shaikhoology.test', 'superadmin'],
    ['Aaliyah Admin', 'admin@shaikhoology.test', 'admin'],
    ['Sheikh Yusuf', 'mufti@shaikhoology.test', 'mufti'],
    ['Sara Analyst', 'user@shaikhoology.test', 'user'],
];
$stmt = $pdo->prepare("INSERT OR IGNORE INTO users (name, email, password, role, active) VALUES (:name, :email, :password, :role, 1)");
foreach ($users as [$name, $email, $role]) {
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hash,
        ':role' => $role,
    ]);
}

$flagStmt = $pdo->prepare("INSERT OR IGNORE INTO feature_flags (key, value, label) VALUES (:key, :value, :label)");
$flagStmt->execute([':key' => 'discussions_enabled', ':value' => 1, ':label' => 'Discussions enabled']);
$flagStmt->execute([':key' => 'suggest_ratios_enabled', ':value' => 1, ':label' => 'Suggest ratios enabled']);
$flagStmt->execute([':key' => 'new_nav_enabled', ':value' => 1, ':label' => 'New navigation']);

$pdo->exec("INSERT OR REPLACE INTO screening_buckets (company_id, bucket, reason, updated_by) 
    SELECT id, 'watch', 'Demo override for showcase', 1 FROM companies WHERE ticker='TCS'");

echo "Seeded SQLite with TCS + one filing\n";
