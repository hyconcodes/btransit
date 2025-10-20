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

## Detailed Documentation

### Overview
- Campus ride-hailing platform with roles for passengers, drivers, and superadmins.
- Emphasis on safe driver onboarding, availability control, and admin oversight.

### Features
- Driver lifecycle: registration, admin approval, availability toggling.
- Vehicle management: name, plate, photo stored via `public` disk.
- Ride management: listing, statuses, and payment history per ride.
- Admin tools: driver details modal, rides modal, approval and availability controls.
- UX: modal-based interfaces with Flux components and Livewire Volt state.

### Roles
- Drivers: manage availability, vehicle info, and handle rides assigned.
- Superadmins: approve/disable drivers, view details, inspect rides and payments.

### Modules
- `Drivers` — onboarding, approval, availability, vehicle info.
- `Rides` — trip records with pickup/destination, fare, status, and payments.
- `Payments` — per-ride payment entries and status tracking.
- `Ratings` — model available for feedback features (not yet surfaced in UI).

### Data Model
- `users` — base identity with name, email, optional phone and avatar.
- `drivers` — links to `users`, includes `status`, `is_available`, `vehicle_*`, and `vehicle_last_updated_at`.
- `rides` — references `driver_id`, fields for route, fare, status, payment metadata, optional schedule.
- `payments` — references `ride_id`, amount, method, status.
- `ratings` — optional feedback scaffold for future features.

### Components
- `resources/views/livewire/admin-drivers.blade.php`
  - Lists drivers with vehicle and status info.
  - Actions: Approve/Disable, availability toggle with spinner, View Rides, View Details.
  - Modals:
    - Rides modal: shows driver rides and payment history.
    - Driver details modal: shows personal details and vehicle info (photo if available).
- `resources/views/livewire/dashboard-driver.blade.php` (driver dashboard)
  - Vehicle modal for register/update.
  - 30‑day update lock (`vehicle_last_updated_at`) with UI messaging and disabled submit.
  - Availability toggle and “Manage Rides” access.

### UI/UX
- Button stack for admin actions: vertical, full‑width, clear states.
- Availability toggle: icon state + loading spinner during Livewire action.
- Modals: `flux:modal` components bound via `wire:model` for clean open/close behavior.

### Workflows
- Driver approval: superadmin toggles status between `approved` and `pending`.
- Availability toggle: updates `drivers.is_available` and reflects state immediately.
- Vehicle update lock: updates allowed once every 30 days, tracked via `vehicle_last_updated_at`.
- Rides inspection: superadmin opens modal to view rides and per‑ride payments.

### Migrations (Key)
- `2025_10_20_110000_add_vehicle_photo_to_drivers_table`
- `2025_10_20_120500_add_vehicle_last_updated_at_to_drivers_table`
- Core tables for users, drivers, rides, payments, ratings, and support columns.

### Security
- No secrets in repo; configure per‑environment `.env`.
- Restrict uploaded file types and use `php artisan storage:link` for safe public access.
- Disable debug in production; use HTTPS and least‑privilege DB accounts.

### Operations
- Clear and warm caches when deploying (`config:cache`, `route:cache`, `view:cache`).
- Use `optimize:clear` to re‑set caches across updates.
- Consider a queue worker for mail or future async tasks.

### Testing
- Run test suite with `php artisan test`.
- Add feature tests for driver approval, availability toggle, and vehicle lock logic.

### Recent Changes
- Converted driver vehicle form to modal with 30‑day lock.
- Added superadmin driver details modal with vehicle photo.
- Stacked action buttons and replaced availability text button with icon + spinner.
- Removed problematic modal `@close` attribute; rely on Livewire binding.
- Added migrations for vehicle photo and vehicle last updated timestamp.

### Roadmap
- Passenger booking UI and matching.
- Enhanced payment integrations and reconciliation.
- Driver analytics and rating surfaces.
- Role‑based authorization hardening and audit logging.