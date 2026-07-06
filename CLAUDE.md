# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CSC RO VIII - Recruitment Portal — a Laravel 13 + Inertia.js (Vue 3) SPA for the Civil Service Commission Regional Office VIII. It implements the full HRMPSB (HR Merit Promotion and Selection Board) recruitment pipeline: applicants browse vacancies and submit applications; an HRMPSB board screens, examines, interviews, and deliberates on candidates; an Appointing Authority makes the final selection; admins oversee users, audit logs, and system configuration.

## Commands

### First-time setup
```bash
composer run setup
# Runs: composer install, .env copy, key:generate, migrate, npm install, npm run build
```

### Development (all services together)
```bash
composer run dev
# Runs concurrently: php artisan serve --port=8010 | queue:listen | npm run dev (Vite HMR)
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
`tests/Feature/` currently has `ApplicationSubmissionTest.php` and `CsFormGenerationTest.php`; `tests/Unit/` is just the stock example.

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

1. XAMPP routes all traffic to `public/index.php` (Laravel entry point).
2. `bootstrap/app.php` configures routing (`routes/web.php` + `routes/api.php`), the three custom middleware aliases (`role`, `admin-access`, `pipeline-stage`), and exception handling.
3. **Web requests** → `routes/web.php` closures → `Inertia::render(...)` → the single Blade shell (`resources/views/app.blade.php`) → Vue SPA boots via `resources/js/app.js`.
4. **API requests** (`/api/*`) → `routes/api.php` → JSON controllers. This is where real authorization is enforced (see below) — `web.php` page routes mostly carry **no** server-side auth middleware.

### Inertia.js pattern

The app is a true SPA using Inertia. Page components live in `resources/js/Pages/`, grouped to match the route structure: `Admin/` (with `Applications/`, `Vacancies/` subfolders), `Applicant/` (with `Profile/`), `Hrmpsb/`, `AppointingAuthority/`, `Auth/`, `Public/`, `Vacancies/`. `Inertia::render('Hrmpsb/QsMatrix')` maps to `Pages/Hrmpsb/QsMatrix.vue`, etc.

`HandleInertiaRequests::share()` shares `auth.user` (full user model) and `auth.profile_complete` on every page — this is how the Vue frontend does its client-side role gating, since most `web.php` page routes have no server guard. Server-side authorization on mutations happens through the API.

### Backend structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/` | 32 controllers covering the full pipeline (vacancies, applications, examinations, interviews, QS/BEI/CBWE/EOPT ratings, background checks, deliberation, appointing-authority decisions, CS forms, email templates, reports/exports) |
| `app/Models/` | 30 Eloquent models — see "Recruitment pipeline models" below |
| `app/Policies/` | `VacancyPolicy`, `ApplicationPolicy`, `DocumentPolicy`, `EvaluationPolicy` — registered via `Gate::policy()`/`Gate::define()` in `AppServiceProvider::boot()` (no `AuthServiceProvider` in Laravel 13) |
| `app/Http/Middleware/EnsureRole.php` | `role:<list>` alias — 403s unless `$user->role` is in the given list |
| `app/Http/Middleware/EnsureAdminAccess.php` | `admin-access` alias — delegates to `User::canAccessAdminModule()` |
| `app/Http/Middleware/EnsurePipelineStageAccessible.php` | `pipeline-stage:<key>` alias — delegates to `PipelineStageService`; gates *workflow progression*, not role |
| `app/Services/PipelineStageService.php` | Central stage-gating logic — see below |
| `app/Services/AuditLog.php` | Static `record(action, model)` helper; writes to `audit_logs`; silently swallows errors |
| `app/Services/AnonymizationService.php` | Issues/unmasks `AnonymizationToken`s for blind deliberation |
| `app/Services/FormGeneratorService.php` | Renders CS Form 33-A/33-B/Form-1 PDFs (DomPDF, views in `resources/views/forms/`) into `CsForm` records |
| `app/Services/PnpkiService.php` | Stub for PNPKI digital signatures on CS Forms — `sign()` just sets `signed_at`, no real crypto yet |
| `app/Services/EmailTemplateMailBuilder.php` | Renders admin-configurable `EmailTemplate` rows into `MailMessage`s, with a generic fallback if none configured |
| `app/Services/HrmpsbCompositionService.php` | Merges global `HrmbsboardComposition` rows with the per-vacancy dynamic "Head of Unit" role |
| `app/Http/Resources/VacancyResource.php` | JSON:API-style resource transformer for vacancy responses |
| `app/Http/Requests/StoreVacancyRequest.php` | Only form-request class; other controllers mostly call `$request->validate([...])` inline |

### Frontend structure

| Path | Purpose |
|------|---------|
| `resources/js/app.js` | Inertia bootstrap; Pinia; global `ProgressBar`/`ToastContainer`/`ConfirmDialog`; `useSessionExpiry()`/`useIdleTimer()` composables run globally; axios base URL is rewritten to `window.location.origin` to survive subpath/proxy deployments; `inertia:error` listener toasts on 419/403 |
| `resources/js/Pages/` | Full-page components, grouped by area (see above) |
| `resources/js/Layouts/` | `PublicLayout.vue`, `ApplicantLayout.vue`, `AdminLayout.vue`, `HrmbsboardLayout.vue`, `AppointingAuthorityLayout.vue` |
| `resources/js/services/api.js` | Axios instance; attaches `Bearer` token from `localStorage['auth_token']`; on 401 clears auth and redirects to `/login?next=...` |

Tailwind v4 is used via `@tailwindcss/vite`. Build-time CSS warnings about unknown `@theme`/`@plugin`/`@source` rules from the LightningCSS minifier are harmless.

### Authentication & authorization

API auth uses Laravel Sanctum (bearer tokens). The token is stored in `localStorage` and attached via the axios interceptor in `api.js`.

**Roles** (`users.role` enum) — collapsed by migration `2026_06_27_100000_simplify_users_role_to_three.php` down to exactly three values: `applicant`, `hrmpsb`, `admin`. (Older values like `hr-officer`/`hr-manager`/`hrmpsb-member`/`appointing-authority` existed transiently and were migrated into `admin`/`hrmpsb` — don't expect to see them in current data.) `UserController::ALLOWED_ROLES` enforces this same set on create/update.

**Finer-grained HRMPSB roles live outside `users.role`**, in the `hrmpsb_compositions` table (model `HrmbsboardComposition`), which is now global rather than per-vacancy. `HrmbsboardComposition::ROLES` includes `chairperson`, `secretariat`, `appointing-authority`, `director-representative`, `division-chief-representative`, `hr-chief`, `pintig-representative-1st`, `pintig-representative-2nd`, plus a dynamically-resolved `head-of-unit` role per vacancy's place of assignment (resolved by `HrmpsbCompositionService`, excluding HRD/ORD).

**`User::canAccessAdminModule()`** (app/Models/User.php) is the crux of the whole authorization scheme — true if `role === 'admin'`, or if `role === 'hrmpsb'` *and* the user holds an active `secretariat`/`hr-chief` designation (`hasAdminDesignation()`). Almost every policy and the `admin-access` middleware defer to it.

Authorization is enforced in three layers, all on the API side:
1. **Route middleware** (`routes/api.php`): `auth:sanctum` (any authenticated user), `admin-access` (admin module), `role:hrmpsb,admin` (the bulk of pipeline-stage endpoints).
2. **Policies/Gates** (registered in `AppServiceProvider::boot()`): `VacancyPolicy` (delete is admin-only, not just admin-module), `ApplicationPolicy` (owners can view their own application via `applicantProfile->id`), `DocumentPolicy` (verify), and gate-based `EvaluationPolicy` (`evaluate-application`, `lock-evaluation`, `unmask-identities` — driven by active `hrmpsb_compositions` rows, since access depends on board membership, not ownership).
3. **`pipeline-stage:<key>` middleware** on `hrmpsb/*` web routes — gates workflow *progression* (has the prerequisite stage been locked/completed?), independent of role.

Password reset requires: min 8 chars, at least one uppercase, one lowercase, one number (`Password::min(8)->letters()->mixedCase()->numbers()`). Non-remember logins expire after 2 hours; remember-me and Google OAuth tokens never expire. Google OAuth is wired via Socialite but `GOOGLE_CLIENT_ID`/`SECRET`/`REDIRECT_URI` are empty in `.env` by default.

### Recruitment pipeline models

`Application` is the central record (`belongsTo Vacancy`, `belongsTo ApplicantProfile as applicant` — **`Application.applicant_id` is a FK to `applicant_profiles`, not `users`**) and `hasMany`/`hasOne` to nearly every stage model below. Pipeline order, per `PipelineStageService::PREREQUISITES`:

```
pre-assessment → qs → twe (written exam) → cbwe → bei → eopt → background → deliberation → (appointing-authority decision)
```

| Model | Represents |
|---|---|
| `PreAssessment` | Initial screening before formal QS |
| `QsEvaluation` | Qualification Standards screening per evaluator; boolean "meets" flags + `overall_qualified`; `locked_at` |
| `ExamResult` | Written exam (TWE) score per application |
| `CbweRating` | Competency-Based Written Exam rating (JSON `competency_scores` against `CbweRating::COMPETENCY_KEYS`) — replaced an earlier written-CBWE approach |
| `BeiRating` | Behavioral Event Interview score (JSON `competency_scores` against the 5 CSC competencies in `BeiRating::COMPETENCIES`); `locked_at` |
| `EoptResult` | Employee orientation/personality test (Big-Five-style traits, `EoptResult::CATEGORIES` × `RATINGS` scale) |
| `BackgroundCheck` | HR-conducted checklist (employment/education/character-ref/NBI); `locked_at` |
| `BackgroundInvestigationReport` | External investigator's report, submitted via a tokenized public upload link (`token`/`token_expires_at`) |
| `AnonymizationToken` | Masks applicant identity for blind deliberation; `unmasked_at`/`unmasked_by` |
| `DeliberationResult` | HRMPSB board's ranking/action per application within a vacancy's deliberation; `locked_at` |
| `ComparativeAssessmentResult` | Generated comparison file/PDF summarizing candidates per vacancy |
| `AppointingAuthorityDecision` | Appointing Authority's final decision on a deliberated application |
| `CsForm` | Generated CSC forms (`CsForm::TYPES`: `33A`, `33B`, `form1`); `signed_at` via the `PnpkiService` stub, `submitted_to_csc_at` |
| `HrmbsboardComposition` | Assigns a `User` to a global HRMPSB role (see roles above) |

Most stage tables have a `locked_at` column gating downstream progression — `PipelineStageService::resolveFlags()` computes ~15 boolean flags per vacancy by querying these tables (note: `qs_exists`/`qs_locked` intentionally consider *all* application IDs while most other flags exclude withdrawn applications). If an upstream "locked" flag is missing but the stage's own data already exists, `OWN_DATA_FALLBACK` still allows access (defensive fallback for legacy/seeded data).

### User name & profile fields

`first_name`, `last_name`, `middle_name`, `suffix` live only on `User` (the legacy composite `name` column and the duplicate name columns on `applicant_profiles` were both dropped). `ApplicantProfile` exposes `first_name`/`last_name`/etc. as accessors that proxy to the related `User`. `User::full_name` is a computed accessor (`"{first} {middle_initial}. {last}, {suffix}"`).

`ApplicantProfile` has a single `birthday` date column (the old duplicate `birthdate` column is gone). `ApplicantProfile::isComplete()` checks `gender`, `civil_status`, `birthday`, `mobile_number`, `region`, `eligibility`. `ApplicantProfile::hasRequiredDocuments()` requires PDS, application letter, COE, TOR (IPCR is optional). `ApplicantProfile.user_id` has a unique DB constraint — one profile per user.

### Status enums

**Application status**: `submitted`, `under_review`, `exam_scheduled`, `interviewed`, `passed`, `failed`, `withdrawn`, plus additional values accepted by `ApplicationController::updateStatus()`: `screened`, `qualified`, `disqualified`, `shortlisted`, `for_interview`, `recommended`, `appointed`, `completed`. `ApplicationStatusUpdated::KNOWN_STATUSES` lists 14 known statuses.

**Vacancy status**: `draft`, `published`, `closed`, `filled` — stored as `VARCHAR(50)` (changed from ENUM; that migration's `down()` is a no-op and irreversible). `Vacancy` has `published()` and `open()` (published + deadline not passed) scopes; `Vacancy` and `Application` are both soft-deleted.

### Notifications

All notification classes implement `ShouldQueue` (`composer run dev` runs `queue:listen`; production needs a persistent `queue:work`). Custom flows worth knowing:
- `VerifyEmail` / password reset use a 6-digit-code flow, not Laravel's default signed-URL/notification.
- `ApplicationStatusUpdated` is the generic lifecycle notification, templated via `EmailTemplateMailBuilder`, and supports a `silent` (database-only) mode.
- `BackgroundInvestigationRequest` emails the named investigator/referee a tokenized upload link consumed by the public, unauthenticated background-investigation-report routes.

### Database

MySQL database (local: `csc_recruitment`, root/no password under XAMPP). Session, cache, and queue all use the `database` driver. Migrations are additive — check migration dates when something looks contradictory; the `role` enum, `vacancies.status`, and the name-column layout have each gone through multiple superseding migrations, so trust the model + latest migration over older ones.

### Routes summary

`routes/web.php` — Inertia page routes only, mostly ungated server-side (client-side guards + the fact that underlying API calls 401/403). Notable exception: `hrmpsb/*` pages are individually gated by `pipeline-stage:<key>` middleware for stage keys `pre-assessment`, `qs`, `twe`, `cbwe`, `bei`, `eopt`, `background`, `deliberation`.

`routes/api.php` — where real enforcement lives:
- Public: vacancy list/show/status-counts, testimonials, competency list, visitor count; login/register are throttled.
- `auth:sanctum`: account/profile/self-service endpoints (my-applications, documents, notifications, feedback).
- `auth:sanctum + admin-access`: vacancy CRUD, HR-side application review, examinations/interviews management, `admin/*` (users, audit logs, dashboard stats, competency library, HRMPSB composition management, email templates), `exports/*`.
- `auth:sanctum + role:hrmpsb,admin`: the bulk of the pipeline — QS, exam/BEI scheduling & results, CBWE, EOPT, pre-assessment, deliberation, comparative assessment, appointing-authority decisions, CS forms, background checks/investigation reports.

Dashboard stats are cached for 1 hour via `Cache::remember`.

### Installed but unused packages

`pinia` is now actually used (see `app.js`). `cropperjs`, `maatwebsite/excel`, and `spatie/laravel-backup` remain installed but not integrated — verify before assuming otherwise.
