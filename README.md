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
