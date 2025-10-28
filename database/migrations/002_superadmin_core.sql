-- Sprint MVP: Superadmin Core (Edit/Delete + Source Visibility)

-- Update financial_series table with new fields
ALTER TABLE financial_series ADD COLUMN source VARCHAR(16) DEFAULT 'manual';
ALTER TABLE financial_series ADD COLUMN confidence VARCHAR(8) DEFAULT 'med';
ALTER TABLE financial_series ADD COLUMN provenance_json TEXT NULL;
ALTER TABLE financial_series ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE financial_series ADD COLUMN deleted_at DATETIME NULL;

-- Create ratio_history table for audit trail
CREATE TABLE ratio_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    financial_series_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    period VARCHAR(16) NOT NULL,
    key VARCHAR(64) NOT NULL,
    old_value DECIMAL(20,4) NULL,
    new_value DECIMAL(20,4) NULL,
    change_note TEXT NULL,
    actor_user_id INTEGER NOT NULL,
    action VARCHAR(16) NOT NULL, -- edit | delete | restore | import | engine
    provenance_json TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (financial_series_id) REFERENCES financial_series(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_ratio_history_financial_series_id ON ratio_history(financial_series_id);
CREATE INDEX idx_ratio_history_company_id ON ratio_history(company_id);
CREATE INDEX idx_ratio_history_action ON ratio_history(action);

-- Create source_adapters registry
CREATE TABLE source_adapters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(32) NOT NULL UNIQUE,
    label VARCHAR(255) NOT NULL,
    base_url VARCHAR(512) NULL,
    enabled TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default adapters
INSERT INTO source_adapters (code, label, base_url, enabled) VALUES
('screener', 'Screener.in', 'https://www.screener.in', 1),
('nse', 'NSE India', 'https://www.nseindia.com', 1),
('bse', 'BSE India', 'https://www.bseindia.com', 1),
('xbrl', 'XBRL Filings', NULL, 0),
('manual_import', 'Manual CSV Import', NULL, 1);

-- Update engine_runs table with adapter info
ALTER TABLE engine_runs ADD COLUMN adapter_code VARCHAR(32) NULL;
ALTER TABLE engine_runs ADD COLUMN provenance_json TEXT NULL;

-- Create indexes for performance
CREATE INDEX idx_financial_series_source ON financial_series(source);
CREATE INDEX idx_financial_series_deleted_at ON financial_series(deleted_at);
CREATE INDEX idx_financial_series_updated_at ON financial_series(updated_at);