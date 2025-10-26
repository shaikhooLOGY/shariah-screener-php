# Shaikhoology — Shari'ah Screener (PHP Skeleton)

Assets-based ratios, tri-bucket gating (Green-only).

## Quick start
1) Copy `.env.example` → `.env` and fill DB creds.
2) `composer install`
3) Serve `public/` (Apache/Nginx) or `php -S localhost:8000 -t public`.
4) Import MySQL DDL (see provided SQL) or wire your DB.

## Notes
- Company Profile has 3 tabs (same page).
- Ratios: debt/interest/liquid/nonsh (assets-based).
- Only 'permissible' companies are computed.

## Superadmin quick start
- Seed demo data (creates sample companies + users with roles):
  ```bash
  composer seed:sqlite
  ```
- Demo accounts (password `secret`):
  - super@shaikhoology.test (superadmin)
  - admin@shaikhoology.test (admin)
  - mufti@shaikhoology.test (mufti)
  - user@shaikhoology.test (user)
- Run screening engine locally:
  ```bash
  composer serve
  # open http://127.0.0.1:8081/dashboard/superadmin/engine
  ```
  Use "Dry run" to preview ratios; full run logs under the job history table.
