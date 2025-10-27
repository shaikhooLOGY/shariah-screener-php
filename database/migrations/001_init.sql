-- Sprint 1: RBAC Abilities + Two-Person Approvals

-- Abilities table
CREATE TABLE abilities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key VARCHAR(128) NOT NULL UNIQUE,
    label VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Role abilities mapping
CREATE TABLE role_abilities (
    role VARCHAR(32) NOT NULL,
    ability_key VARCHAR(128) NOT NULL,
    PRIMARY KEY (role, ability_key),
    FOREIGN KEY (ability_key) REFERENCES abilities(key) ON DELETE CASCADE
);
CREATE INDEX idx_role_abilities_role ON role_abilities(role);

-- User abilities mapping (overrides)
CREATE TABLE user_abilities (
    user_id INTEGER NOT NULL,
    ability_key VARCHAR(128) NOT NULL,
    PRIMARY KEY (user_id, ability_key),
    FOREIGN KEY (ability_key) REFERENCES abilities(key) ON DELETE CASCADE
);
CREATE INDEX idx_user_abilities_user_id ON user_abilities(user_id);

-- Approvals table for two-person approvals
CREATE TABLE approvals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kind VARCHAR(64) NOT NULL, -- e.g., 'cmv.publish','cmv.rollback','user.ban'
    entity VARCHAR(64) NOT NULL, -- table/entity name
    entity_id INTEGER NULL, -- entity primary key
    requested_by INTEGER NOT NULL, -- user id
    approver_id INTEGER NULL, -- user id
    status TEXT CHECK(status IN ('pending','approved','rejected')) NOT NULL DEFAULT 'pending',
    payload_json TEXT NULL, -- additional data as JSON
    note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    decided_at DATETIME NULL
);
CREATE INDEX idx_approvals_status ON approvals(status);
CREATE INDEX idx_approvals_requested_by ON approvals(requested_by);
CREATE INDEX idx_approvals_approver_id ON approvals(approver_id);

-- Existing tables (keeping for compatibility)
CREATE TABLE IF NOT EXISTS companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    isin VARCHAR(24) UNIQUE,
    ticker VARCHAR(32) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    sector VARCHAR(120),
    status VARCHAR(32) DEFAULT 'pending'
);

CREATE TABLE IF NOT EXISTS filings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    period VARCHAR(20) NOT NULL,
    total_assets DECIMAL(20,4),
    total_debt DECIMAL(20,4),
    cash_and_equivalents DECIMAL(20,4),
    receivables DECIMAL(20,4),
    interest_income DECIMAL(20,4),
    non_shariah_income DECIMAL(20,4),
    revenue DECIMAL(20,4),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_company_period (company_id, period)
);

CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug VARCHAR(190) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    body LONGTEXT,
    published_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS scholars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug VARCHAR(190) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    bio TEXT,
    credentials TEXT
);

-- Users table (assuming it exists from previous setup)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(32) DEFAULT 'user',
    active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    actor_id INTEGER NULL,
    action VARCHAR(255) NOT NULL,
    entity VARCHAR(255) NOT NULL,
    entity_id VARCHAR(255) NOT NULL,
    meta TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sprint 2: Compliance Master Versioning (CMV)

-- CMV versions table
CREATE TABLE compliance_master_versions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label VARCHAR(64) NOT NULL,
    period VARCHAR(16) NOT NULL,
    status TEXT CHECK(status IN ('draft','published','archived','rolled_back')) NOT NULL DEFAULT 'draft',
    note TEXT NULL,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_cmv_status ON compliance_master_versions(status);
CREATE INDEX idx_cmv_period ON compliance_master_versions(period);

-- CMV current pointers
CREATE TABLE compliance_master_current (
    id INTEGER PRIMARY KEY DEFAULT 1,
    cmv_id_published INTEGER NULL,
    cmv_id_pending INTEGER NULL
);

-- CMV results table
CREATE TABLE cmv_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cmv_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    period VARCHAR(16) NOT NULL,
    business_activity_json TEXT NULL,
    financial_json TEXT NULL,
    behaviour_json TEXT NULL,
    final_score REAL NOT NULL,
    verdict VARCHAR(16) NOT NULL,
    breaches_json TEXT NULL,
    computed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
CREATE INDEX idx_cmv_results_cmv_id ON cmv_results(cmv_id);
CREATE INDEX idx_cmv_results_company_id ON cmv_results(company_id);

-- CMV diffs table
CREATE TABLE cmv_diffs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cmv_id INTEGER NOT NULL,
    stats_json TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE
);

-- Sprint 3: Tasks, Ratio Suggestions, Controversies

-- Tasks table
CREATE TABLE tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    type TEXT CHECK(type IN ('activity_review','ratio_review','controversy','mapping')) NOT NULL,
    company_id INTEGER NOT NULL,
    payload_json TEXT NULL,
    priority TEXT CHECK(priority IN ('low','med','high')) DEFAULT 'med',
    assignee_id INTEGER NULL,
    status TEXT CHECK(status IN ('open','in_progress','done','blocked')) DEFAULT 'open',
    sla_at DATETIME NULL,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_assignee_id ON tasks(assignee_id);
CREATE INDEX idx_tasks_company_id ON tasks(company_id);

-- Mufti profiles
CREATE TABLE mufti_profiles (
    user_id INTEGER PRIMARY KEY,
    expertise_sectors_json TEXT NULL,
    bio TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ratio suggestions
CREATE TABLE ratio_suggestions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    suggested_by INTEGER NOT NULL,
    period VARCHAR(16) NOT NULL,
    payload_json TEXT NOT NULL,
    source TEXT DEFAULT 'user',
    screener_link TEXT NULL,
    status TEXT CHECK(status IN ('pending','accepted','rejected')) DEFAULT 'pending',
    reviewer_id INTEGER NULL,
    review_note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (suggested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX idx_ratio_suggestions_company_status ON ratio_suggestions(company_id, status);

-- Financial series (manual fills and suggestions)
CREATE TABLE financial_series (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    period VARCHAR(16) NOT NULL,
    key VARCHAR(64) NOT NULL,
    value DECIMAL(20,4) NOT NULL,
    source TEXT CHECK(source IN ('engine','manual','suggestion')) NOT NULL,
    by_user INTEGER NOT NULL,
    confidence TEXT CHECK(confidence IN ('low','med','high')) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (by_user) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_financial_series_company_period_key ON financial_series(company_id, period, key);

-- Controversy queue
CREATE TABLE controversy_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    topic VARCHAR(255) NOT NULL,
    context_json TEXT NULL,
    status TEXT CHECK(status IN ('open','closed')) DEFAULT 'open',
    opened_by INTEGER NOT NULL,
    opened_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Controversy votes
CREATE TABLE controversy_votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    controversy_id INTEGER NOT NULL,
    mufti_id INTEGER NOT NULL,
    vote TEXT CHECK(vote IN ('compliant','noncompliant','grey')) NOT NULL,
    note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (controversy_id) REFERENCES controversy_queue(id) ON DELETE CASCADE,
    FOREIGN KEY (mufti_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(controversy_id, mufti_id)
);

-- Activity history
CREATE TABLE activity_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    snapshot_json TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Sprint 4: Sectoral Compliance + Ulama

-- Sectors table
CREATE TABLE sectors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    is_compliant INTEGER NOT NULL DEFAULT 1,
    rationale TEXT NULL,
    updated_by INTEGER NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Company sector mapping
CREATE TABLE company_sector_map (
    company_id INTEGER NOT NULL,
    sector_id INTEGER NOT NULL,
    PRIMARY KEY (company_id, sector_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE CASCADE
);
