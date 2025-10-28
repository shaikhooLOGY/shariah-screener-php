<?php
declare(strict_types=1);

$dsn = 'sqlite:' . __DIR__ . '/../storage/shaikhoology.sqlite';
$pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys = ON;');

/* users table */
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'user',
  active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT DEFAULT (datetime('now'))
);
");

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

/* seed companies if missing */
$companyExists = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE ticker='TCS'")->fetchColumn();
if ($companyExists === 0) {
  $pdo->exec("INSERT OR IGNORE INTO companies (isin, ticker, name)
  VALUES ('INE467B01029','TCS','Tata Consultancy Services');");
}

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

/* create CMV tables */
$pdo->exec("CREATE TABLE IF NOT EXISTS compliance_master_versions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL,
    period TEXT NOT NULL,
    status TEXT CHECK(status IN ('draft','published','archived','rolled_back')) NOT NULL DEFAULT 'draft',
    note TEXT NULL,
    created_by INTEGER NOT NULL,
    created_at TEXT DEFAULT (datetime('now'))
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS compliance_master_current (
    id INTEGER PRIMARY KEY DEFAULT 1,
    cmv_id_published INTEGER NULL,
    cmv_id_pending INTEGER NULL
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS cmv_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cmv_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    period TEXT NOT NULL,
    business_activity_json TEXT NULL,
    financial_json TEXT NULL,
    behaviour_json TEXT NULL,
    final_score REAL NOT NULL,
    verdict TEXT NOT NULL,
    breaches_json TEXT NULL,
    computed_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS cmv_diffs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cmv_id INTEGER NOT NULL,
    stats_json TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE
);");

/* create Sprint 3/4 tables */
$pdo->exec("CREATE TABLE IF NOT EXISTS sectors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    is_compliant INTEGER NOT NULL DEFAULT 1,
    rationale TEXT NULL,
    updated_by INTEGER NOT NULL,
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS company_sector_map (
    company_id INTEGER NOT NULL,
    sector_id INTEGER NOT NULL,
    PRIMARY KEY (company_id, sector_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    type TEXT CHECK(type IN ('activity_review','ratio_review','controversy','mapping')) NOT NULL,
    company_id INTEGER NOT NULL,
    payload_json TEXT NULL,
    priority TEXT CHECK(priority IN ('low','med','high')) DEFAULT 'med',
    assignee_id INTEGER NULL,
    status TEXT CHECK(status IN ('open','in_progress','done','blocked')) DEFAULT 'open',
    sla_at TEXT NULL,
    created_by INTEGER NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS mufti_profiles (
    user_id INTEGER PRIMARY KEY,
    expertise_sectors_json TEXT NULL,
    bio TEXT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS ratio_suggestions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    suggested_by INTEGER NOT NULL,
    period TEXT NOT NULL,
    payload_json TEXT NOT NULL,
    source TEXT DEFAULT 'user',
    screener_link TEXT NULL,
    status TEXT CHECK(status IN ('pending','accepted','rejected')) DEFAULT 'pending',
    reviewer_id INTEGER NULL,
    review_note TEXT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    reviewed_at TEXT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (suggested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS financial_series (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    period TEXT NOT NULL,
    key TEXT NOT NULL,
    value REAL NOT NULL,
    source TEXT CHECK(source IN ('engine','manual','suggestion')) NOT NULL,
    by_user INTEGER NOT NULL,
    confidence TEXT CHECK(confidence IN ('low','med','high')) NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (by_user) REFERENCES users(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS controversy_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    topic TEXT NOT NULL,
    context_json TEXT NULL,
    status TEXT CHECK(status IN ('open','closed')) DEFAULT 'open',
    opened_by INTEGER NOT NULL,
    opened_at TEXT DEFAULT (datetime('now')),
    closed_at TEXT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by) REFERENCES users(id) ON DELETE CASCADE
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS controversy_votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    controversy_id INTEGER NOT NULL,
    mufti_id INTEGER NOT NULL,
    vote TEXT CHECK(vote IN ('compliant','noncompliant','grey')) NOT NULL,
    note TEXT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (controversy_id) REFERENCES controversy_queue(id) ON DELETE CASCADE,
    FOREIGN KEY (mufti_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(controversy_id, mufti_id)
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS activity_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    snapshot_json TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);");

/* seed abilities and role mappings */
$pdo->exec("INSERT OR IGNORE INTO abilities (key, label) VALUES
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

$pdo->exec("INSERT OR IGNORE INTO role_abilities (role, ability_key) VALUES
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
('admin', 'activity.view_history'),
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

/* seed users with hashed passwords */
$secretHash = password_hash('secret', PASSWORD_DEFAULT);
$pdo->exec("INSERT OR IGNORE INTO users (name, email, password_hash, role, active) VALUES
('Shaikh Super', 'super@shaikhoology.test', '{$secretHash}', 'superadmin', 1),
('Admin User', 'admin@shaikhoology.test', '{$secretHash}', 'admin', 1),
('Mufti Expert', 'mufti@shaikhoology.test', '{$secretHash}', 'mufti', 1),
('Regular User', 'user@shaikhoology.test', '{$secretHash}', 'user', 1);");

/* seed demo users for impersonation */
$pdo->exec("INSERT OR IGNORE INTO users (name, email, password_hash, role, active) VALUES
('Demo Superadmin', 'super@demo.com', '{$secretHash}', 'superadmin', 1),
('Demo Admin', 'admin@demo.com', '{$secretHash}', 'admin', 1),
('Demo Mufti', 'mufti@demo.com', '{$secretHash}', 'mufti', 1),
('Demo User', 'user@demo.com', '{$secretHash}', 'user', 1);");

/* seed CMV demo data */
$pdo->exec("INSERT OR IGNORE INTO compliance_master_versions (label, period, status, note, created_by) VALUES
('2025-Q2 Published', '2025-Q2', 'published', 'Initial published CMV for demo', 1),
('2025-Q3 Draft', '2025-Q3', 'draft', 'Draft CMV for testing', 1);");

$cmvPublishedId = (int)$pdo->query("SELECT id FROM compliance_master_versions WHERE status='published' LIMIT 1")->fetchColumn();
$cmvDraftId = (int)$pdo->query("SELECT id FROM compliance_master_versions WHERE status='draft' LIMIT 1")->fetchColumn();

if ($cmvPublishedId) {
    $pdo->exec("INSERT OR IGNORE INTO compliance_master_current (id, cmv_id_published) VALUES (1, {$cmvPublishedId}) ON CONFLICT(id) DO UPDATE SET cmv_id_published=excluded.cmv_id_published;");
}

if ($cmvPublishedId) {
    $tcsId = (int)$pdo->query("SELECT id FROM companies WHERE ticker='TCS' LIMIT 1")->fetchColumn();
    if ($tcsId) {
        $pdo->exec("INSERT OR IGNORE INTO cmv_results (cmv_id, company_id, period, business_activity_json, financial_json, final_score, verdict, breaches_json) VALUES
        ({$cmvPublishedId}, {$tcsId}, '2025-Q2', '{\"sector\":\"IT\",\"activity_score\":0.9}', '{\"debt_pct\":0.25,\"interest_pct\":0.02,\"liquid_pct\":0.95}', 0.85, 'compliant', '[\"none\"]');");
    }
}

if ($cmvDraftId) {
    $pdo->exec("INSERT OR IGNORE INTO cmv_diffs (cmv_id, stats_json) VALUES ({$cmvDraftId}, '{\"moved\":{\"compliant_to_grey\":1},\"by_sector\":{\"IT\":\"+1G\"},\"counts\":{\"compliant\":0,\"grey\":1,\"noncompliant\":0}}');");
}

/* seed sectors */
$pdo->exec("INSERT OR IGNORE INTO sectors (name, is_compliant, rationale, updated_by) VALUES
('Information Technology', 1, 'Generally compliant sector with low Shari''ah concerns', 1),
('Healthcare', 1, 'Essential services, generally compliant', 1),
('Financial Services', 0, 'Interest-based activities make this sector non-compliant', 1),
('Energy', 1, 'Oil & Gas sector with varying compliance levels', 1);");

/* seed company sector mappings */
$itSectorId = (int)$pdo->query("SELECT id FROM sectors WHERE name='Information Technology' LIMIT 1")->fetchColumn();
if ($itSectorId && $tcsId) {
    $pdo->exec("INSERT OR IGNORE INTO company_sector_map (company_id, sector_id) VALUES ({$tcsId}, {$itSectorId});");
}

/* seed mufti profiles */
$muftiId = (int)$pdo->query("SELECT id FROM users WHERE role='mufti' LIMIT 1")->fetchColumn();
if ($muftiId) {
    $pdo->exec("INSERT OR IGNORE INTO mufti_profiles (user_id, expertise_sectors_json, bio) VALUES
    ({$muftiId}, '[\"Information Technology\",\"Healthcare\"]', 'Experienced Islamic finance scholar specializing in technology and healthcare sectors.')");
}

/* seed sample tasks */
if ($tcsId && $muftiId) {
    $pdo->exec("INSERT OR IGNORE INTO tasks (title, type, company_id, payload_json, priority, assignee_id, status, created_by) VALUES
    ('Review TCS activity compliance', 'activity_review', {$tcsId}, '{\"focus\":\"business_activities\"}', 'high', {$muftiId}, 'open', 1),
    ('Validate TCS financial ratios', 'ratio_review', {$tcsId}, '{\"period\":\"2025-Q2\"}', 'med', {$muftiId}, 'pending', 1);");
}

/* seed sample ratio suggestion */
if ($tcsId) {
    $userId = (int)$pdo->query("SELECT id FROM users WHERE role='user' LIMIT 1")->fetchColumn();
    if ($userId) {
        $pdo->exec("INSERT OR IGNORE INTO ratio_suggestions (company_id, suggested_by, period, payload_json, source, screener_link, status) VALUES
        ({$tcsId}, {$userId}, '2025-Q2', '{\"debt_pct\":0.23,\"interest_pct\":0.015}', 'user', 'https://example.com/source', 'pending');");
    }
}

echo "SQLite discussion+suggestions+abilities+cmv+users+sectors+tasks ready.\n";
