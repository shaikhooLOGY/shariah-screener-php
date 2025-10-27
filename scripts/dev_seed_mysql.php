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

// Create abilities tables
$pdo->exec("CREATE TABLE IF NOT EXISTS abilities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(128) NOT NULL UNIQUE,
    label VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("CREATE TABLE IF NOT EXISTS role_abilities (
    role VARCHAR(32) NOT NULL,
    ability_key VARCHAR(128) NOT NULL,
    PRIMARY KEY (role, ability_key),
    FOREIGN KEY (ability_key) REFERENCES abilities(key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$pdo->exec("CREATE INDEX idx_role_abilities_role ON role_abilities(role);");

$pdo->exec("CREATE TABLE IF NOT EXISTS user_abilities (
    user_id BIGINT UNSIGNED NOT NULL,
    ability_key VARCHAR(128) NOT NULL,
    PRIMARY KEY (user_id, ability_key),
    FOREIGN KEY (ability_key) REFERENCES abilities(key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$pdo->exec("CREATE INDEX idx_user_abilities_user_id ON user_abilities(user_id);");

$pdo->exec("CREATE TABLE IF NOT EXISTS approvals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kind VARCHAR(64) NOT NULL,
    entity VARCHAR(64) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    approver_id BIGINT UNSIGNED NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    payload_json TEXT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    decided_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$pdo->exec("CREATE INDEX idx_approvals_status ON approvals(status);");
$pdo->exec("CREATE INDEX idx_approvals_requested_by ON approvals(requested_by);");
$pdo->exec("CREATE INDEX idx_approvals_approver_id ON approvals(approver_id);");

// Seed abilities and role mappings
$pdo->exec("INSERT IGNORE INTO abilities (key, label) VALUES
('watchlist.add', 'Add companies to watchlist'),
('watchlist.remove', 'Remove companies from watchlist'),
('watchlist.view', 'View personal watchlist'),
('discussion.post', 'Post in discussions'),
('discussion.moderate', 'Moderate discussions'),
('suggest.ratio', 'Suggest ratios for companies'),
('activity.update', 'Update company activity status'),
('activity.view_history', 'View activity history'),
('ratios.manual_fill', 'Manually fill ratios'),
('ratios.review_suggestion', 'Review ratio suggestions'),
('controversy.vote', 'Vote on controversies'),
('controversy.finalize', 'Finalize controversies'),
('sector_mapping.edit', 'Edit sector mappings'),
('task.assign_mufti', 'Assign tasks to muftis'),
('task.update', 'Update task status'),
('user.ban', 'Request user ban'),
('user.unban', 'Unban users'),
('role.request_mufti_add_remove', 'Request mufti role changes'),
('cmv.run', 'Run CMV engine'),
('cmv.publish', 'Publish CMV changes'),
('cmv.rollback', 'Rollback CMV changes'),
('role.approve_request', 'Approve role change requests'),
('sector_caps.edit', 'Edit sector caps'),
('cmv.view_diff', 'View CMV diffs');");

$pdo->exec("INSERT IGNORE INTO role_abilities (role, ability_key) VALUES
('user', 'watchlist.add'),
('user', 'watchlist.remove'),
('user', 'watchlist.view'),
('user', 'discussion.post'),
('user', 'suggest.ratio'),
('mufti', 'watchlist.add'),
('mufti', 'watchlist.remove'),
('mufti', 'watchlist.view'),
('mufti', 'discussion.post'),
('mufti', 'suggest.ratio'),
('mufti', 'activity.update'),
('mufti', 'activity.view_history'),
('mufti', 'ratios.manual_fill'),
('mufti', 'ratios.review_suggestion'),
('mufti', 'controversy.vote'),
('mufti', 'sector_mapping.edit'),
('admin', 'watchlist.add'),
('admin', 'watchlist.remove'),
('admin', 'watchlist.view'),
('admin', 'discussion.post'),
('admin', 'suggest.ratio'),
('admin', 'activity.update'),
('activity.view_history'),
('admin', 'ratios.manual_fill'),
('admin', 'ratios.review_suggestion'),
('admin', 'controversy.vote'),
('admin', 'sector_mapping.edit'),
('admin', 'task.assign_mufti'),
('admin', 'task.update'),
('admin', 'user.ban'),
('admin', 'user.unban'),
('admin', 'role.request_mufti_add_remove'),
('superadmin', 'watchlist.add'),
('superadmin', 'watchlist.remove'),
('superadmin', 'watchlist.view'),
('superadmin', 'discussion.post'),
('superadmin', 'suggest.ratio'),
('superadmin', 'activity.update'),
('superadmin', 'activity.view_history'),
('superadmin', 'ratios.manual_fill'),
('superadmin', 'ratios.review_suggestion'),
('superadmin', 'controversy.vote'),
('superadmin', 'sector_mapping.edit'),
('superadmin', 'task.assign_mufti'),
('superadmin', 'task.update'),
('superadmin', 'user.ban'),
('superadmin', 'user.unban'),
('superadmin', 'role.request_mufti_add_remove'),
('superadmin', 'cmv.run'),
('superadmin', 'cmv.publish'),
('superadmin', 'cmv.rollback'),
('superadmin', 'role.approve_request'),
('superadmin', 'sector_caps.edit'),
('superadmin', 'cmv.view_diff');");

// Create CMV tables
$pdo->exec("CREATE TABLE IF NOT EXISTS compliance_master_versions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(64) NOT NULL,
    period VARCHAR(16) NOT NULL,
    status ENUM('draft','published','archived','rolled_back') NOT NULL DEFAULT 'draft',
    note TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$pdo->exec("CREATE INDEX idx_cmv_status ON compliance_master_versions(status);");
$pdo->exec("CREATE INDEX idx_cmv_period ON compliance_master_versions(period);");

$pdo->exec("CREATE TABLE IF NOT EXISTS compliance_master_current (
    id BIGINT UNSIGNED PRIMARY KEY DEFAULT 1,
    cmv_id_published BIGINT UNSIGNED NULL,
    cmv_id_pending BIGINT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("CREATE TABLE IF NOT EXISTS cmv_results (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cmv_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    period VARCHAR(16) NOT NULL,
    business_activity_json TEXT NULL,
    financial_json TEXT NULL,
    behaviour_json TEXT NULL,
    final_score DECIMAL(3,2) NOT NULL,
    verdict VARCHAR(16) NOT NULL,
    breaches_json TEXT NULL,
    computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$pdo->exec("CREATE INDEX idx_cmv_results_cmv_id ON cmv_results(cmv_id);");
$pdo->exec("CREATE INDEX idx_cmv_results_company_id ON cmv_results(company_id);");

$pdo->exec("CREATE TABLE IF NOT EXISTS cmv_diffs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cmv_id BIGINT UNSIGNED NOT NULL,
    stats_json TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed CMV demo data
$pdo->exec("INSERT IGNORE INTO compliance_master_versions (label, period, status, note, created_by) VALUES
('2025-Q2 Published', '2025-Q2', 'published', 'Initial published CMV for demo', 1),
('2025-Q3 Draft', '2025-Q3', 'draft', 'Draft CMV for testing', 1);");

$cmvPublishedId = (int)$pdo->query("SELECT id FROM compliance_master_versions WHERE status='published' LIMIT 1")->fetchColumn();
$cmvDraftId = (int)$pdo->query("SELECT id FROM compliance_master_versions WHERE status='draft' LIMIT 1")->fetchColumn();

if ($cmvPublishedId) {
    $pdo->exec("INSERT IGNORE INTO compliance_master_current (id, cmv_id_published) VALUES (1, {$cmvPublishedId});");
}

if ($cmvPublishedId) {
    $pdo->exec("INSERT IGNORE INTO cmv_results (cmv_id, company_id, period, business_activity_json, financial_json, final_score, verdict, breaches_json)
    SELECT {$cmvPublishedId}, c.id, '2025-Q2', '{\"sector\":\"IT\",\"activity_score\":0.9}', '{\"debt_pct\":0.25,\"interest_pct\":0.02,\"liquid_pct\":0.95}', 0.85, 'compliant', '[\"none\"]'
    FROM companies c WHERE c.ticker='TCS' LIMIT 1;");
}

if ($cmvDraftId) {
    $pdo->exec("INSERT IGNORE INTO cmv_diffs (cmv_id, stats_json) VALUES ({$cmvDraftId}, '{\"moved\":{\"compliant_to_grey\":1},\"by_sector\":{\"IT\":\"+1G\"},\"counts\":{\"compliant\":0,\"grey\":1,\"noncompliant\":0}}');");
}

echo "MySQL seed done.\n";
