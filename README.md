# bTransit

A campus-focused ride-hailing platform built with Laravel and Livewire. This project provides driver management, ride tracking, payments, and an admin interface for operational oversight.

## Features
- Driver registration, approval, and availability management
- Vehicle info with photo uploads stored on the `public` disk
- Ride listing with basic payment history
- Admin tools: view driver details, rides, and toggle statuses
- Clean, modal-based UI components using Flux and Livewire Volt

## Tech Stack
- Backend: Laravel, PHP 8.2+
- Frontend: Blade, Livewire (Volt), Tailwind CSS, Vite
- Database: MySQL/PostgreSQL (configurable)

## Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ and npm
- A local database (MySQL/PostgreSQL) with credentials you control

## Quick Start (Local)
1. Clone the repository.
2. Copy environment file:
   - `cp .env.example .env` (Windows PowerShell: `Copy-Item .env.example .env`)
3. Set local environment variables in `.env` (do not commit this file):
   - `APP_NAME`, `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://127.0.0.1:8000`
   - `DB_CONNECTION` and DB credentials for your local database
   - `FILESYSTEM_DISK=public`
   - Optional mail settings for notifications (use safe test accounts)
4. Install dependencies:
   - `composer install`
   - `npm install`
5. Generate an app key:
   - `php artisan key:generate`
6. Run migrations and (optionally) seed sample data:
   - `php artisan migrate`
   - `php artisan db:seed` (optional)
7. Link storage for public file access:
   - `php artisan storage:link`
8. Start the dev servers:
   - `npm run dev`
   - `php artisan serve`
9. Visit the app at `http://127.0.0.1:8000`.

## Common Tasks
- Clear caches when editing Blade/Livewire views:
  - `php artisan view:clear`
  - `php artisan cache:clear`
- Run tests:
  - `php artisan test` or `phpunit`

## Configuration Overview
- Environment variables are required for database, mail, and storage disks.
- Do not include secrets or credentials in commits, documentation, or screenshots.
- Use per-environment `.env` files and secure secret managers in production.

## Security & Hardening
- Do not commit `.env` or any secret keys.
- Set `APP_DEBUG=false` and `APP_ENV=production` for production deployments.
- Use strong, unique database credentials with least privilege.
- Serve over HTTPS and terminate TLS at your proxy/load balancer.
- Restrict uploaded file types and size; store uploads under `storage/app/public`.
- Ensure the web server does not execute scripts from upload directories.
- Keep dependencies up to date; apply security patches promptly.
- Enable rate limiting on sensitive endpoints (auth and admin actions).
- Regularly back up the database and rotate credentials.

## Deployment Notes
- Build assets: `npm run build`
- Cache configs and routes for performance:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
- Clear and re-warm caches on updates:
  - `php artisan optimize:clear`
- Configure a queue worker if you enable async jobs.

## Project Structure Highlights
- `app/Models/` — Eloquent models (Driver, Ride, Payment, User)
- `resources/views/livewire/` — Livewire Volt components and admin UI views
- `database/migrations/` — Schema migrations, including drivers, rides, payments
- `storage/app/public/` — Uploaded assets (linked to `public/storage`)

## Contributing
- Open issues or submit pull requests for improvements.
- Avoid including sensitive information in any PRs or discussions.

## License
This project is proprietary to its owner. Do not redistribute without permission.