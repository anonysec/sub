# x-ui / 3x-ui PHP portal (admin + user)

This project now includes:

- Fake public site (`/`) + hidden portal login.
- Admin and user pages for subscription management.
- Node status/load-balance dashboard.
- Optional Iran RSS widget.
- **Offline ticket system** (`/ticket.php` for users, `/tickets.php` for admin).

## Security upgrades included

- CSRF protection + secure session cookie flags.
- Security headers: CSP, X-Frame-Options, nosniff, no-referrer.
- Optional access key gate for hidden login (`ACCESS_KEY`).
- Basic login rate limiting per IP.
- Password hash support (`ADMIN_PASSWORD_HASH`, `USER_PASSWORD_HASH`).
- TLS verification enabled for API and RSS fetch requests.

## Quick start

1. `cp .env.example .env`
2. Set strong values.
3. Serve `public/` as web root.
4. Configure hidden portal rewrite to `/portal.php`.

## Ticket flow

- User opens `/ticket.php` and submits message.
- Tickets are appended to `data/tickets.jsonl`.
- Admin reads tickets at `/tickets.php`.

## Note

For production, use reverse proxy rate-limits, WAF, firewall allow-list, and monitor logs.
