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

echo "SQLite discussion+suggestions+abilities+cmv ready.\n";
