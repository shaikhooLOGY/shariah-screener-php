<?php
declare(strict_types=1);

/**
 * Usage: php scripts/dev_seed_mysql.php host db user pass
 * Example: php scripts/dev_seed_mysql.php 127.0.0.1 shaikhoology root secret
 */
[$script, $host, $db, $user, $pass] = array_pad($argv, 5, null);
if (!$host || !$db || !$user) {
  fwrite(STDERR, "Usage: php scripts/dev_seed_mysql.php host db user pass\n");
  exit(1);
}
$dsn = "mysql:host={$host};port=3306;dbname={$db};charset=utf8mb4";
$pdo = new PDO($dsn, $user, (string)$pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

$pdo->exec("CREATE TABLE IF NOT EXISTS companies (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  isin VARCHAR(24) UNIQUE,
  ticker VARCHAR(50) UNIQUE,
  name VARCHAR(255) NOT NULL,
  sector_code VARCHAR(100),
  country VARCHAR(100) DEFAULT 'India',
  description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("CREATE TABLE IF NOT EXISTS filings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  period VARCHAR(16) NOT NULL,
  filing_date DATE NULL,
  total_assets DECIMAL(20,4),
  total_debt DECIMAL(20,4),
  cash DECIMAL(20,4),
  receivables DECIMAL(20,4),
  revenue DECIMAL(20,4),
  interest_income DECIMAL(20,4),
  non_shariah_income DECIMAL(20,4),
  notes_json JSON NULL,
  CONSTRAINT fk_f_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  UNIQUE KEY uq_company_period (company_id, period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("INSERT IGNORE INTO companies (isin, ticker, name, sector_code, country, description)
VALUES ('INE467B01029','TCS','Tata Consultancy Services','IT','India','Seed row for prod demo');");

$pdo->exec("INSERT INTO filings (
  company_id, period, filing_date, total_assets, total_debt, cash, receivables,
  revenue, interest_income, non_shariah_income, notes_json
)
SELECT id, '2025-Q2', '2025-09-30', 1000.0, 250.0, 300.0, 200.0, 100.0, 2.0, 2.0, JSON_OBJECT('source','seed')
FROM companies WHERE ticker='TCS'
ON DUPLICATE KEY UPDATE filing_date=VALUES(filing_date);");

echo "MySQL seed done.\n";
