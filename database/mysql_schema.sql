CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(32) DEFAULT 'user',
  active TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS companies (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  isin VARCHAR(24) UNIQUE,
  ticker VARCHAR(32) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  sector VARCHAR(120),
  status VARCHAR(32) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS filings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  period VARCHAR(20) NOT NULL,
  total_assets DECIMAL(20,4),
  total_debt DECIMAL(20,4),
  cash_and_equivalents DECIMAL(20,4),
  receivables DECIMAL(20,4),
  interest_income DECIMAL(20,4),
  non_shariah_income DECIMAL(20,4),
  revenue DECIMAL(20,4),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_filings_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  UNIQUE KEY uq_company_period (company_id, period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS articles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(190) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  body LONGTEXT,
  published_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scholars (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(190) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  bio TEXT,
  credentials TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sprint 1: RBAC Abilities + Two-Person Approvals
CREATE TABLE IF NOT EXISTS abilities (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key VARCHAR(128) NOT NULL UNIQUE,
  label VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_abilities (
  role VARCHAR(32) NOT NULL,
  ability_key VARCHAR(128) NOT NULL,
  PRIMARY KEY (role, ability_key),
  FOREIGN KEY (ability_key) REFERENCES abilities(key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_role_abilities_role ON role_abilities(role);

CREATE TABLE IF NOT EXISTS user_abilities (
  user_id BIGINT UNSIGNED NOT NULL,
  ability_key VARCHAR(128) NOT NULL,
  PRIMARY KEY (user_id, ability_key),
  FOREIGN KEY (ability_key) REFERENCES abilities(key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_user_abilities_user_id ON user_abilities(user_id);

CREATE TABLE IF NOT EXISTS approvals (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  kind VARCHAR(64) NOT NULL,
  entity VARCHAR(64) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  requested_by BIGINT UNSIGNED NOT NULL,
  approver_id BIGINT UNSIGNED NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
  payload_json TEXT NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  decided_at TIMESTAMP NULL,
  FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_approvals_status ON approvals(status);
CREATE INDEX idx_approvals_requested_by ON approvals(requested_by);
CREATE INDEX idx_approvals_approver_id ON approvals(approver_id);

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  actor_id BIGINT UNSIGNED NULL,
  action VARCHAR(255) NOT NULL,
  entity VARCHAR(255) NOT NULL,
  entity_id VARCHAR(255) NOT NULL,
  meta TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sprint 2: Compliance Master Versioning (CMV)
CREATE TABLE IF NOT EXISTS compliance_master_versions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  label VARCHAR(64) NOT NULL,
  period VARCHAR(16) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived','rolled_back')),
  note TEXT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_cmv_status ON compliance_master_versions(status);
CREATE INDEX idx_cmv_period ON compliance_master_versions(period);

CREATE TABLE IF NOT EXISTS compliance_master_current (
  id BIGINT UNSIGNED PRIMARY KEY DEFAULT 1,
  cmv_id_published BIGINT UNSIGNED NULL,
  cmv_id_pending BIGINT UNSIGNED NULL,
  FOREIGN KEY (cmv_id_published) REFERENCES compliance_master_versions(id) ON DELETE SET NULL,
  FOREIGN KEY (cmv_id_pending) REFERENCES compliance_master_versions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cmv_results (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_cmv_results_cmv_id ON cmv_results(cmv_id);
CREATE INDEX idx_cmv_results_company_id ON cmv_results(company_id);

CREATE TABLE IF NOT EXISTS cmv_diffs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cmv_id BIGINT UNSIGNED NOT NULL,
  stats_json TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cmv_id) REFERENCES compliance_master_versions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sprint 3: Tasks, Ratio Suggestions, Controversies
CREATE TABLE IF NOT EXISTS tasks (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  type VARCHAR(32) NOT NULL CHECK (type IN ('activity_review','ratio_review','controversy','mapping')),
  company_id BIGINT UNSIGNED NOT NULL,
  payload_json TEXT NULL,
  priority VARCHAR(16) DEFAULT 'med' CHECK (priority IN ('low','med','high')),
  assignee_id BIGINT UNSIGNED NULL,
  status VARCHAR(32) DEFAULT 'open' CHECK (status IN ('open','in_progress','done','blocked')),
  sla_at TIMESTAMP NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_assignee_id ON tasks(assignee_id);
CREATE INDEX idx_tasks_company_id ON tasks(company_id);

CREATE TABLE IF NOT EXISTS mufti_profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  expertise_sectors_json TEXT NULL,
  bio TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ratio_suggestions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  suggested_by BIGINT UNSIGNED NOT NULL,
  period VARCHAR(16) NOT NULL,
  payload_json TEXT NOT NULL,
  source VARCHAR(32) DEFAULT 'user',
  screener_link TEXT NULL,
  status VARCHAR(32) DEFAULT 'pending' CHECK (status IN ('pending','accepted','rejected')),
  reviewer_id BIGINT UNSIGNED NULL,
  review_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (suggested_by) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_ratio_suggestions_company_status ON ratio_suggestions(company_id, status);

CREATE TABLE IF NOT EXISTS financial_series (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  period VARCHAR(16) NOT NULL,
  key VARCHAR(64) NOT NULL,
  value DECIMAL(20,4) NOT NULL,
  source VARCHAR(32) NOT NULL CHECK (source IN ('engine','manual','suggestion')),
  by_user BIGINT UNSIGNED NOT NULL,
  confidence VARCHAR(16) NOT NULL CHECK (confidence IN ('low','med','high')),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (by_user) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_financial_series_company_period_key ON financial_series(company_id, period, key);

CREATE TABLE IF NOT EXISTS controversy_queue (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  topic VARCHAR(255) NOT NULL,
  context_json TEXT NULL,
  status VARCHAR(32) DEFAULT 'open' CHECK (status IN ('open','closed')),
  opened_by BIGINT UNSIGNED NOT NULL,
  opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  closed_at TIMESTAMP NULL,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (opened_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS controversy_votes (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  controversy_id BIGINT UNSIGNED NOT NULL,
  mufti_id BIGINT UNSIGNED NOT NULL,
  vote VARCHAR(16) NOT NULL CHECK (vote IN ('compliant','noncompliant','grey')),
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (controversy_id) REFERENCES controversy_queue(id) ON DELETE CASCADE,
  FOREIGN KEY (mufti_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_controversy_mufti (controversy_id, mufti_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_history (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  snapshot_json TEXT NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sprint 4: Sectoral Compliance + Ulama
CREATE TABLE IF NOT EXISTS sectors (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL UNIQUE,
  is_compliant TINYINT NOT NULL DEFAULT 1,
  rationale TEXT NULL,
  updated_by BIGINT UNSIGNED NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS company_sector_map (
  company_id BIGINT UNSIGNED NOT NULL,
  sector_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (company_id, sector_id),
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
