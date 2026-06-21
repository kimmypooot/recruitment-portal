# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CSC Regional Office Recruitment Portal — a Laravel 13 + Inertia.js (Vue 3) SPA for the Civil Service Commission. Applicants browse vacancies and submit applications; HR officers manage the pipeline; admins oversee users and audit logs.

## Commands

### First-time setup
```bash
composer run setup
# Runs: composer install, .env copy, key:generate, migrate, npm install, npm run build
```

### Development (all services together)
```bash
composer run dev
# Runs concurrently: php artisan serve | queue:listen | pail (log tail) | npm run dev (Vite HMR)
```

### Frontend only
```bash
npm run dev      # Vite dev server with HMR
npm run build    # Production build → public/build/
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh --seed   # Wipe and reseed
```

### Tests
```bash
composer run test                  # Clears config cache then runs PHPUnit
php artisan test --filter TestName # Single test
```

### Code style
```bash
./vendor/bin/pint                  # Laravel Pint (PHP CS Fixer)
```

### Utilities
```bash
php artisan optimize:clear         # Clear all caches (config, route, view, events)
php artisan route:list             # Inspect registered routes
php artisan tinker                 # REPL
```

## Architecture

### Request flow

1. XAMPP routes all traffic to `public/index.php` (Laravel entry point)
2. `bootstrap/app.php` configures routing (`routes/web.php` + `routes/api.php`), middleware, and exception handling
3. **Web requests** (`/`) → `routes/web.php` closure → `Inertia::render('Home')` → `resources/views/app.blade.php` (single Blade shell) → Vue SPA boots via `resources/js/app.js`
4. **API requests** (`/api/*`) → `routes/api.php` → JSON controllers

### Inertia.js pattern

The app is a true SPA using Inertia. There is only one Blade view (`resources/views/app.blade.php`). Page components live in `resources/js/Pages/`. The Inertia resolver maps controller calls like `Inertia::render('Home')` to `Pages/Home.vue`, `Inertia::render('Vacancies/Apply')` to `Pages/Vacancies/Apply.vue`, etc.

Props passed to `Inertia::render()` arrive as Vue `defineProps` in the page component. Server-side data (e.g. initial vacancies on the homepage) is passed this way to avoid a loading flash; subsequent filtering/pagination uses `axios` calls to `/api/*`.

### Backend structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/` | 9 controllers; all return `JsonResponse` (API) or `Inertia\Response` (web) |
| `app/Models/` | Eloquent models: `User`, `Vacancy`, `Application`, `ApplicantProfile`, `Document`, `ExamSchedule`, `InterviewSchedule` |
| `app/Notifications/` | 5 queued notifications (mail + database channels); use Laravel 13 `#[Tries]` / `#[Timeout]` attributes |
| `app/Services/AuditLog.php` | Static `record(action, model)` helper; writes to `audit_logs` table; silently swallows errors |
| `app/Http/Resources/VacancyResource.php` | JSON:API-style resource transformer for vacancy responses |
| `app/Http/Requests/StoreVacancyRequest.php` | Form request with vacancy validation rules |

### Frontend structure

| Path | Purpose |
|------|---------|
| `resources/js/app.js` | Inertia bootstrap; auto-resolves `Pages/**/*.vue` |
| `resources/js/Pages/` | Full-page components — one per route |
| `resources/js/Layouts/PublicLayout.vue` | Wraps all public pages; sticky navbar + footer |
| `resources/js/Components/Vacancy/VacancyCard.vue` | Card displayed in the vacancy grid |
| `resources/js/Components/UI/StatusBadge.vue` | Coloured pill for vacancy/application status values |
| `resources/js/Components/UI/NotificationBell.vue` | Bell icon for in-app notifications |
| `resources/js/services/api.js` | Axios instance pre-configured with CSRF + `/api` base URL; exports `vacancyApi` and `applicationApi` |

Tailwind v4 is used via `@tailwindcss/vite`. The CSS warnings during `npm run build` about unknown `@theme`/`@plugin`/`@source` rules from LightningCSS minifier are harmless — Tailwind handles them before minification.

### Authentication & roles

API auth uses Laravel Sanctum (token-based). The `User` model has a `role` column: `applicant`, `hr-officer`, `hr-manager`, `admin`. API route groups apply `auth:sanctum` middleware. The `role` middleware is referenced in routes but needs to be registered if the custom `role` middleware is added.

### Database

MySQL database `csc_recruitment` (root, no password for local XAMPP). Key tables: `users`, `vacancies`, `applications`, `applicant_profiles`, `documents`, `exam_schedules` (via `ExamSchedule` model), `audit_logs`, `notifications` (Laravel's built-in notification table). Session, cache, and queue all use the `database` driver.

**Note:** The two vacancies migrations (`2026_06_19_131050` and `2026_06_19_131658`) both target the `vacancies` table — the second one is a stub and will fail if the first already ran. The full schema for vacancies is defined in the `Vacancy` model's `$fillable`; the migration needs the actual columns added.

### Notifications queue

All 5 notification classes implement `ShouldQueue`. The `composer run dev` command runs `queue:listen` alongside the server. In production, run a persistent queue worker (`php artisan queue:work`).
