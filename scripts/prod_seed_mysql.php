<?php
declare(strict_types=1);

/**
 * Usage: php scripts/prod_seed_mysql.php host db user pass
 * Example: php scripts/prod_seed_mysql.php localhost shaikhoology admin secret
 */
[$script, $host, $db, $user, $pass] = array_pad($argv, 5, null);
if (!$host || !$db || !$user) {
  fwrite(STDERR, "Usage: php scripts/prod_seed_mysql.php host db user pass\n");
  exit(1);
}

$dsn = "mysql:host={$host};port=3306;dbname={$db};charset=utf8mb4";
$pdo = new PDO($dsn, $user, (string)$pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$schema = file_get_contents(__DIR__ . '/../database/mysql_schema.sql');
$pdo->exec($schema);

$pdo->exec("
INSERT INTO companies (ticker, name, sector, status)
VALUES ('TCS', 'Tata Consultancy Services', 'IT Services', 'active')
ON DUPLICATE KEY UPDATE name=VALUES(name), sector=VALUES(sector), status=VALUES(status);
");

$companyId = (int)$pdo->query("SELECT id FROM companies WHERE ticker='TCS'")->fetchColumn();

$stmt = $pdo->prepare("
INSERT INTO filings (
  company_id, period, total_assets, total_debt, cash_and_equivalents, receivables,
  interest_income, non_shariah_income, revenue
)
VALUES (:company_id, :period, :assets, :debt, :cash, :receivables, :interest, :nonsh, :revenue)
ON DUPLICATE KEY UPDATE
  total_assets=VALUES(total_assets),
  total_debt=VALUES(total_debt),
  cash_and_equivalents=VALUES(cash_and_equivalents),
  receivables=VALUES(receivables),
  interest_income=VALUES(interest_income),
  non_shariah_income=VALUES(non_shariah_income),
  revenue=VALUES(revenue)
");
$stmt->execute([
  ':company_id' => $companyId,
  ':period' => '2025-Q2',
  ':assets' => 1000.0,
  ':debt' => 250.0,
  ':cash' => 300.0,
  ':receivables' => 200.0,
  ':interest' => 2.0,
  ':nonsh' => 2.0,
  ':revenue' => 100.0,
]);

$pdo->exec("
INSERT INTO scholars (slug, name, bio, credentials)
VALUES ('example-scholar', 'Dr. Fatima Al-Faruqi', 'Senior Shari\'ah advisor focusing on equities.', 'AAOIFI, IIFA')
ON DUPLICATE KEY UPDATE name=VALUES(name), bio=VALUES(bio), credentials=VALUES(credentials);
");

$pdo->exec("
INSERT INTO articles (slug, title, body, published_at)
VALUES ('why-ratios-matter', 'Why Ratios Matter', 'Add rich article content later.', NOW())
ON DUPLICATE KEY UPDATE title=VALUES(title), body=VALUES(body), published_at=VALUES(published_at);
");

echo "Production MySQL seed completed.\n";
