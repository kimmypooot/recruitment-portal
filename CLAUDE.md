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
| `app/Models/` | Eloquent models: `User`, `Vacancy`, `Application`, `ApplicantProfile`, `Document`, `ExamSchedule`, `InterviewSchedule`, `WorkExperience`, `EducationalAttainment`, `Training` |
| `app/Notifications/` | 5 queued notifications (mail + database channels); use Laravel 13 `#[Tries]` / `#[Timeout]` attributes |
| `app/Services/AuditLog.php` | Static `record(action, model)` helper; writes to `audit_logs` table; silently swallows errors |
| `app/Http/Resources/VacancyResource.php` | JSON:API-style resource transformer for vacancy responses |
| `app/Http/Requests/StoreVacancyRequest.php` | Only form request class; all other controllers use `$request->validated()` without declaring rules |

### Frontend structure

| Path | Purpose |
|------|---------|
| `resources/js/app.js` | Inertia bootstrap; auto-resolves `Pages/**/*.vue` |
| `resources/js/Pages/` | Full-page components — one per route |
| `resources/js/Layouts/` | `PublicLayout.vue`, `ApplicantLayout.vue`, `AdminLayout.vue` |
| `resources/js/Components/DataPrivacyModal.vue` | Rendered in root app component; shown globally |
| `resources/js/services/api.js` | Axios instance pre-configured with CSRF + `/api` base URL; exports `vacancyApi` and `applicationApi` |

Tailwind v4 is used via `@tailwindcss/vite`. The CSS warnings during `npm run build` about unknown `@theme`/`@plugin`/`@source` rules from LightningCSS minifier are harmless — Tailwind handles them before minification.

### Authentication & roles

API auth uses Laravel Sanctum (token-based). Sanctum token is stored in `localStorage` on the frontend and attached as a `Bearer` token via an axios interceptor in `api.js`. The `User` model has a `role` column with values: `applicant`, `hr-officer`, `hr-manager`, `admin`.

No custom role-checking middleware exists. Route groups only apply `auth:sanctum`; role enforcement is manual inside controllers (where it exists at all — see Known Issues).

Google OAuth (`/auth/google`, `/auth/google/callback`) is wired via Laravel Socialite but the `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` values are empty in `.env`.

### Key model relationships

- `User` → `hasOne(ApplicantProfile)` (applicants only; HR/admin users have no profile)
- `ApplicantProfile` → `hasMany(Application)` — **`Application.applicant_id` is a FK to `applicant_profiles`, not `users`**
- `Application` → `hasMany(Document)`, `hasMany(ExamSchedule)`, `hasMany(InterviewSchedule)`
- `ApplicantProfile` → `hasMany(WorkExperience)`, `hasMany(EducationalAttainment)`, `hasMany(Training)`
- `Vacancy` → soft-deleted (`SoftDeletes`); `Application` → soft-deleted

`Vacancy` has scopes: `published()` and `open()` (published + deadline not yet passed).

`ApplicantProfile::isComplete()` checks only 6 fields: `gender`, `civil_status`, `birthday`, `mobile_number`, `region`, `eligibility`.

### Status enums

**Application status** (stored as string): `submitted`, `under_review`, `exam_scheduled`, `interviewed`, `passed`, `failed`, `withdrawn`
— `ApplicationController::updateStatus()` also accepts: `screened`, `qualified`, `disqualified`, `shortlisted`, `for_interview`, `recommended`, `appointed`, `completed`

**Vacancy status**: `draft`, `published`, `closed`, `filled`

### Routes summary

Web routes serve Inertia pages:
- Public: `/`, `/how-to-apply`, `/about`, `/login`, `/register`
- Applicant (auth): `/applicant/dashboard`, `/applicant/applications`, `/applicant/complete-profile`, `/vacancies/{id}/apply`
- Admin (auth): `/admin/dashboard`, `/admin/vacancies`, `/admin/applications`, `/admin/users`, `/admin/audit-logs`
- File serving: `/profile/photo`, `/profile/documents/{path}` (token-authenticated, owner-only)

API routes are under `/api/*` with `auth:sanctum` for authenticated endpoints. Dashboard stats are cached for 1 hour via `Cache::remember`.

### Database

MySQL database `csc_recruitment` (root, no password for local XAMPP). Session, cache, and queue all use the `database` driver.

Migrations are additive: the base `applicant_profiles` table (June 19) has only a few columns; the June 21 migration adds the full set of profile fields. The `ApplicantProfile` model has a duplicate column issue: both `birthday` and `birthdate` exist (the newer migrations use `birthday`; use that one).

**Note:** The two vacancies migrations (`2026_06_19_131050` and `2026_06_19_131658`) both target the `vacancies` table — the second one is a stub and will fail if the first already ran.

### Notifications queue

All 5 notification classes implement `ShouldQueue`. The `composer run dev` command runs `queue:listen` alongside the server. In production, run a persistent queue worker (`php artisan queue:work`).

### Audit logging

`AuditLog::record(string $action, Model $model)` inserts to `audit_logs` with the current user's id. It silently catches all exceptions. Currently only called from `VacancyController` (created, published, archived, deleted).

### Installed but unused packages

`pinia` (Vue state store), `cropperjs` (image cropper), `maatwebsite/excel`, and `spatie/laravel-backup` are installed but not integrated.

## Known Issues

These are active gaps in the codebase — not things to work around but things to be aware of before touching the affected areas:

1. **No authorization policies exist.** `VacancyController` calls `$this->authorize('create', Vacancy::class)` etc., but no `VacancyPolicy` class is defined. These calls will throw an `AuthorizationException` in production.

2. **`ApplicationStatusUpdated` notification is broken at runtime.** It accesses `$this->application->applicant->full_name`, but `Application::applicant` resolves to `ApplicantProfile` (not `User`), and `ApplicantProfile` has no `full_name` attribute.

3. **`UserFactory` missing role state methods.** `Feature/ApplicationSubmissionTest.php` calls `.applicant()` and `.hrOfficer()` factory states that don't exist — the test suite will fail.

4. **`ExaminationController` and `InterviewController` have no validation or auth checks.** They call `$request->validated()` without any `rules()` — it returns an empty array silently.

5. **`UserController` has no role-based protection.** Any authenticated user can list/create/update all users via `/api/admin/users`.
