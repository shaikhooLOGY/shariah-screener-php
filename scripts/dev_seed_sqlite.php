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

echo "Seeded SQLite with TCS + one filing\n";
