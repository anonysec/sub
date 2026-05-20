# x-ui / 3x-ui PHP portal (cPanel-friendly)

You can run this on **shared hosting with cPanel** (no VPS required).

## cPanel deployment (recommended)

1. Upload project to: `~/xui-portal/`
2. Set web root to: `~/xui-portal/public` (or copy only `public/*` into `public_html`)
3. Create `~/xui-portal/.env` from `.env.example`
4. Make sure these are writable:
   - `~/xui-portal/tmp/`
   - `~/xui-portal/data/tickets/`
5. Keep `src/` and `.env` **outside public_html** whenever possible.

## If you must use `public_html`

- Upload `public/.htaccess` to `public_html/.htaccess`.
- Move sensitive files (`.env`, `src/`, `data/`, `tmp/`) outside `public_html` if your host allows.
- If not possible, deny access using `.htaccess` rules.

## Features

- Hidden portal login (`portal.php` + optional `ACCESS_KEY`)
- Admin page for x-ui actions
- User subscription check page
- Node status/load-balance view
- Per-user offline tickets in `data/tickets/<user_id>.jsonl`
- Modern dark/night UI
- Telegram support contact in ticket page (`https://t.me/imKoris`)

## cPanel notes

- `cfg()` now also reads `getenv()` so you can configure values in cPanel environment settings.
- Session files are stored under `tmp/sessions` automatically.
- Use cron jobs in cPanel later if you want cleanup/report tasks.

## Local preview (optional)

```bash
cp .env.example .env
./preview.sh 8080
```
