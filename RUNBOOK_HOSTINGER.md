# Hostinger Deploy Runbook

This guide covers both Git deploys and manual ZIP uploads for `/domains/halal.shaikhoology.com/public_html`.

## One-time setup
1. hPanel → Advanced → **SSH Access** → ensure an SSH key exists; add as GitHub Deploy Key (read-only).
2. (Optional Git) hPanel → Advanced → **GIT** → Connect  
   Repo: `git@github.com:shaikhoology/shariah-screener-php.git`  
   Branch: `main`  
   Directory: `/domains/halal.shaikhoology.com/public_html`
3. Confirm the document root is exactly `/domains/halal.shaikhoology.com/public_html` (no nested `public_html`).

## Building the package (local workstation)
1. From repo root run:
   ```
   composer install
   composer build:prod
   ```
2. The build script outputs a ZIP inside `dist/shaikhoology_build_YYYYMMDD_HHMM.zip`. This archive already includes `vendor/`, `public/`, `scripts/`, `.htaccess`, and deploy docs.

## Manual upload deploy
1. Create (or confirm) the MySQL database + user in hPanel. Note DB name, username, password.
2. Upload the latest ZIP to `/domains/halal.shaikhoology.com/public_html/` via File Manager or FTP and **Extract**.
3. Create `.env` in the same root (alongside `.htaccess`) using `.env.production.example` as a template:
   ```
   APP_ENV=production
   APP_TZ=Asia/Kolkata
   APP_URL=https://halal.shaikhoology.com
   DB_DSN=mysql:host=localhost;port=3306;dbname=shaikhoology
   DB_USER=…    # database user
   DB_PASS=…    # database password
   ```
4. If the database is empty, seed sample data via Web Terminal:
   ```
   php domains/halal.shaikhoology.com/public_html/scripts/prod_seed_mysql.php localhost shaikhoology DB_USER DB_PASS
   ```

## Git-based redeploy (optional)
1. Push to `main` on GitHub.
2. hPanel → GIT → **Deploy**.
3. Ensure files appear in `/domains/halal.shaikhoology.com/public_html` after deployment.

## Sanity checks (browser or Web Terminal)
- `https://halal.shaikhoology.com/prod-ok.txt` → should display **ok**.
- `https://halal.shaikhoology.com/prod-health` → expect `{"status":"up","db":"ok","env":"production"}`.
- `https://halal.shaikhoology.com/company/TCS` → renders the company profile with ratios.

If `prod-ok.txt` fails → document root misconfigured.  
If `prod-ok.txt` works but `prod-health` 404 → root `.htaccess` or rewrite not applied.  
If `prod-health` responds with `fail` → verify `.env` DB credentials or run the seeder.

## Notes
- Root `.htaccess` routes everything through `public/index.php` and blocks sensitive folders.
- `public/.htaccess` handles front-controller rules within `public/`; keep both files.
- Use `scripts/prod_check.php` for quick CLI verification once APP_URL is configured.
