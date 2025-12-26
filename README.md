# POS Sales Ledger (Phase 1 MVP)

Trustworthy sales ledger built on Laravel 12 + Inertia + Vue 3 (TS).

## Local development

1. `docker-compose up -d`
2. `cp .env.example .env`
3. `php artisan key:generate`
4. `composer install`
5. `php artisan migrate --seed`
6. `npm install`
7. `npm run dev`

Default owner (created by seed):

- Email: `owner@example.com`
- Password: `password`

## Notes

- PHP 8.3 is required.
- PostgreSQL 15+ and Redis are required. SQLite/MySQL are not used.
- Queue connection is Redis (`QUEUE_CONNECTION=redis`).
- Roles seeded: Owner, Manager, Sales.
- Inventory negative stock toggle: `INVENTORY_ALLOW_NEGATIVE=false`.
