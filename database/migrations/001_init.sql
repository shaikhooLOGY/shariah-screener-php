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
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
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
    status ENUM('draft','published','archived','rolled_back') NOT NULL DEFAULT 'draft',
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
