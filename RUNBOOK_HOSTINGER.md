# Hostinger Deploy Runbook

## One-time
1. hPanel → Advanced → SSH Access → ensure a key exists; add as GitHub Deploy Key (read-only).
2. hPanel → Advanced → GIT → Connect:
   - Repo: git@github.com:shaikhoology/shariah-screener-php.git
   - Branch: main
   - Directory: /domains/halal.shaikhoology.com/public_html
3. Ensure `/domains/halal.shaikhoology.com/public_html` is clean (no stray nested `public_html/`).

## Each deploy
1. Push to `main` on GitHub.
2. hPanel → GIT → **Deploy**.
3. Verify files exist in `/domains/halal.shaikhoology.com/public_html` (root), including `public/`.

## Env + vendors
1. Create `.env` in root (public_html), not inside `/public`:

```
APP_ENV=production
APP_TZ=Asia/Kolkata
APP_URL=https://halal.shaikhoology.com
DB_DSN=mysql:host=localhost;port=3306;dbname=shaikhoology
DB_USER=…
DB_PASS=…
```

2. SSH available:

```
cd domains/halal.shaikhoology.com/public_html
composer install --no-dev --optimize-autoloader
```

*No SSH?* Run Composer locally then upload the `vendor/` directory to the same root.

## Sanity checks (browser or Web Terminal)
- `https://halal.shaikhoology.com/prod-ok.txt` → **ok**
- `https://halal.shaikhoology.com/prod-health` → `{"status":"up","db":"ok","env":"production"}`
- `https://halal.shaikhoology.com/company/TCS` → renders profile
- If `prod-ok.txt` fails → document root wrong.
- If `prod-ok.txt` works but `prod-health` 404 → check root `.htaccess` rewrite.
- If `prod-health` error → check `.env` DB creds or `vendor/` presence.

## Notes
- Root `.htaccess` rewrites to `public/index.php` and blocks sensitive paths.
- `public/.htaccess` also exists for local runs; both coexist safely.
- Use Hostinger Git “Auto Deployment” if you want pull-on-push.
