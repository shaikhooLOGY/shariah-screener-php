<?php
declare(strict_types=1);

$dsn = 'sqlite:' . __DIR__ . '/../storage/shaikhoology.sqlite';
$pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$pdo->exec('PRAGMA foreign_keys = ON;');

$pdo->exec("
CREATE TABLE IF NOT EXISTS companies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  isin TEXT UNIQUE,
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
CREATE TABLE IF NOT EXISTS abilities (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  key TEXT NOT NULL UNIQUE,
  label TEXT NOT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS role_abilities (
  role TEXT NOT NULL,
  ability_key TEXT NOT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (role, ability_key)
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS user_abilities (
  user_id INTEGER NOT NULL,
  ability_key TEXT NOT NULL,
  assigned_at TEXT DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, ability_key),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
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
CREATE TABLE IF NOT EXISTS approvals (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  kind TEXT NOT NULL,
  entity TEXT NOT NULL,
  entity_id INTEGER,
  requested_by INTEGER NOT NULL,
  approver_id INTEGER,
  status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','approved','rejected')),
  payload_json TEXT,
  note TEXT,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  decided_at TEXT,
  FOREIGN KEY(requested_by) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(approver_id) REFERENCES users(id) ON DELETE SET NULL
);
");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_approvals_status ON approvals(status);");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_role_abilities_role ON role_abilities(role);");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_abilities_user ON user_abilities(user_id);");

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

$abilities = [
    'watchlist.*' => 'Access personal watchlists',
    'discussion.post' => 'Post in company discussions',
    'suggest.ratio' => 'Submit ratio suggestions',
    'activity.update' => 'Update screening activities',
    'activity.view_history' => 'View screening history',
    'ratios.manual_fill' => 'Manually fill ratios',
    'ratios.review_suggestion' => 'Review suggested ratios',
    'controversy.vote' => 'Vote on controversies',
    'sector_mapping.edit' => 'Edit sector mappings',
    'task.assign_mufti' => 'Assign tasks to muftis',
    'task.update' => 'Update screening tasks',
    'user.ban' => 'Request user ban',
    'user.unban' => 'Request user unban',
    'role.request_mufti_add_remove' => 'Request mufti role changes',
    'cmv.run' => 'Run compliance master validation',
    'cmv.publish' => 'Publish compliance master version',
    'cmv.rollback' => 'Rollback compliance master version',
    'role.approve_request' => 'Approve privileged requests',
    'sector_caps.edit' => 'Edit sector caps',
    'cmv.view_diff' => 'View compliance diffs',
    'controversy.finalize' => 'Finalize controversy verdict',
];

$abilityStmt = $pdo->prepare("INSERT OR IGNORE INTO abilities (key, label) VALUES (:key, :label)");
foreach ($abilities as $key => $label) {
    $abilityStmt->execute([':key' => $key, ':label' => $label]);
}

$userAbilities = ['watchlist.*', 'discussion.post', 'suggest.ratio'];
$muftiAbilities = array_merge($userAbilities, [
    'activity.update',
    'activity.view_history',
    'ratios.manual_fill',
    'ratios.review_suggestion',
    'controversy.vote',
    'sector_mapping.edit',
]);
$adminAbilities = array_merge($userAbilities, [
    'task.assign_mufti',
    'task.update',
    'user.ban',
    'user.unban',
    'role.request_mufti_add_remove',
]);
$superadminAbilities = array_values(array_unique(array_merge(
    $muftiAbilities,
    $adminAbilities,
    [
        'cmv.run',
        'cmv.publish',
        'cmv.rollback',
        'role.approve_request',
        'sector_caps.edit',
        'cmv.view_diff',
        'controversy.finalize',
    ]
)));

$roleAbilities = [
    'user' => $userAbilities,
    'mufti' => array_values(array_unique($muftiAbilities)),
    'admin' => array_values(array_unique($adminAbilities)),
    'superadmin' => $superadminAbilities,
];

$roleAbilityStmt = $pdo->prepare("INSERT OR IGNORE INTO role_abilities (role, ability_key) VALUES (:role, :ability)");
foreach ($roleAbilities as $role => $abilitiesList) {
    foreach ($abilitiesList as $abilityKey) {
        $roleAbilityStmt->execute([':role' => $role, ':ability' => $abilityKey]);
    }
}

$flagStmt = $pdo->prepare("INSERT OR IGNORE INTO feature_flags (key, value, label) VALUES (:key, :value, :label)");
$flagStmt->execute([':key' => 'discussions_enabled', ':value' => 1, ':label' => 'Discussions enabled']);
$flagStmt->execute([':key' => 'suggest_ratios_enabled', ':value' => 1, ':label' => 'Suggest ratios enabled']);
$flagStmt->execute([':key' => 'new_nav_enabled', ':value' => 1, ':label' => 'New navigation']);

$pdo->exec("INSERT OR REPLACE INTO screening_buckets (company_id, bucket, reason, updated_by) 
    SELECT id, 'watch', 'Demo override for showcase', 1 FROM companies WHERE ticker='TCS'");

echo "Seeded SQLite with TCS + one filing\n";
