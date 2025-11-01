-- Performance indexes for Sprint 5
-- SQLite and MySQL compatible

-- Companies table
CREATE INDEX IF NOT EXISTS idx_companies_ticker ON companies(ticker);

-- Filings table
CREATE INDEX IF NOT EXISTS idx_filings_company_id_period ON filings(company_id, period);

-- Ratio suggestions table
CREATE INDEX IF NOT EXISTS idx_ratio_suggestions_company_id_status ON ratio_suggestions(company_id, status);

-- Financial series table
CREATE INDEX IF NOT EXISTS idx_financial_series_company_period_key ON financial_series(company_id, period, key);

-- Tasks table
CREATE INDEX IF NOT EXISTS idx_tasks_status_assignee_company ON tasks(status, assignee_id, company_id);

-- Controversy votes table
CREATE UNIQUE INDEX IF NOT EXISTS idx_controversy_votes_unique ON controversy_votes(controversy_id, mufti_id);