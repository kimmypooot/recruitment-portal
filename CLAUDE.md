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
The seeder creates demo admins, demo applicants, competencies and email templates, but **no HRMPSB board** (no `hrmpsb` users, `hrmpsb_compositions` or `place_of_assignment_heads`). Board members are CSC employees who sign in from IMIS; an admin gives each a role under Admin → HRMPSB. Never run it on production (the demo accounts use `Password123`).

### Tests
```bash
composer run test                  # Clears config cache then runs PHPUnit
php artisan test --filter TestName # Single test
```
`tests/Feature/` holds the suite (about 25 files: pipeline and stage authorization, CS forms, IMIS sign-in/import, conflict of interest, seeding, …); `tests/Unit/` is just the stock example. Feature tests use in-memory SQLite built from the migrations, so a column that only exists in the live MySQL database (not in a migration) is missing there — see "Database".

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
2. `bootstrap/app.php` configures routing (`routes/web.php` + `routes/api.php`), the four custom middleware aliases (`role`, `admin-access`, `pipeline-stage`, `no-conflict`), and exception handling.
3. **Web requests** → `routes/web.php` closures → `Inertia::render(...)` → the single Blade shell (`resources/views/app.blade.php`) → Vue SPA boots via `resources/js/app.js`.
4. **API requests** (`/api/*`) → `routes/api.php` → JSON controllers. This is where real authorization is enforced (see below) — `web.php` page routes mostly carry **no** server-side auth middleware.

### Inertia.js pattern

The app is a true SPA using Inertia. Page components live in `resources/js/Pages/`, grouped to match the route structure: `Admin/` (with `Applications/`, `Vacancies/` subfolders), `Applicant/` (with `Profile/`), `Hrmpsb/`, `AppointingAuthority/`, `Auth/`, `Public/`, `Vacancies/`. `Inertia::render('Hrmpsb/QsMatrix')` maps to `Pages/Hrmpsb/QsMatrix.vue`, etc.

`HandleInertiaRequests::share()` shares `auth.user` (full user model) and `auth.profile_complete` on every page — this is how the Vue frontend does its client-side role gating, since most `web.php` page routes have no server guard. Server-side authorization on mutations happens through the API.

### Backend structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/` | 39 controllers covering the full pipeline (vacancies, applications, examinations, interviews, QS/BEI/CBWE/EOPT ratings, background checks, deliberation, appointing-authority decisions, CS forms, email templates, reports/exports, IMIS sign-in, per-vacancy recusals) |
| `app/Models/` | 34 Eloquent models — see "Recruitment pipeline models" below |
| `app/Policies/` | `VacancyPolicy`, `ApplicationPolicy`, `DocumentPolicy`, `EvaluationPolicy` — registered via `Gate::policy()`/`Gate::define()` in `AppServiceProvider::boot()` (no `AuthServiceProvider` in Laravel 13) |
| `app/Http/Middleware/EnsureRole.php` | `role:<list>` alias — 403s unless `$user->role` is in the given list |
| `app/Http/Middleware/EnsureAdminAccess.php` | `admin-access` alias — delegates to `User::canAccessAdminModule()` |
| `app/Http/Middleware/EnsurePipelineStageAccessible.php` | `pipeline-stage:<key>` alias — delegates to `PipelineStageService`; gates *workflow progression*, not role |
| `app/Http/Middleware/EnsureNoConflictOfInterest.php` | `no-conflict` alias — 403 (`code: conflict_of_interest`) if the user has their own application, or a recusal, on the vacancy being acted on; works out the vacancy from route params and `application_id`/`application_ids`/`vacancy_id` in the body. Applied to the whole `role:hrmpsb,admin` group and the HR-side application status/profile/documents routes — **new evaluation routes must carry it too** |
| `app/Services/PipelineStageService.php` | Central stage-gating logic — see below |
| `app/Services/ConflictOfInterestService.php` | `hasConflict()` (own application or recusal), `recuse()` (idempotent, audit-logged), `recuseApplicantIfStaff()` (called from `ApplicationController::store`) |
| `app/Services/ImisPlantilla.php` | Server-to-server IMIS client (bearer `IMIS_API_TOKEN`): vacant plantilla items and selection write-back; also owns the shared HTTP client (`request()`/`enabled()`) |
| `app/Services/ImisEmployeeRecords.php` | IMIS client for an employee's verified PDS and 201-file documents, keyed by `users.imis_user_id` |
| `app/Services/ImisProfileImporter.php` | Fills an IMIS employee's `ApplicantProfile` from IMIS (add-only, see "IMIS integration") |
| `app/Services/AuditLog.php` | Static `record(action, model)` helper; writes to `audit_logs`; silently swallows errors |
| `app/Services/AnonymizationService.php` | Issues/unmasks `AnonymizationToken`s for blind deliberation |
| `app/Services/FormGeneratorService.php` | Renders CS Form 33-A/33-B/Form-1 PDFs (DomPDF, views in `resources/views/forms/`) into `CsForm` records |
| `app/Services/PnpkiService.php` | Stub for PNPKI digital signatures on CS Forms — `sign()` just sets `signed_at`, no real crypto yet |
| `app/Services/EmailTemplateMailBuilder.php` | Renders admin-configurable `EmailTemplate` rows into `MailMessage`s, with a generic fallback if none configured |
| `app/Services/HrmpsbCompositionService.php` | Merges global `HrmpsbComposition` rows with the per-vacancy dynamic "Head of Unit" role, **minus anyone recused from that vacancy**; `isActiveMember()` is false for the recused; `quorumStatus()` counts principals left against `config('hrmpsb.min_members')` |
| `app/Http/Resources/VacancyResource.php` | JSON:API-style resource transformer for vacancy responses |
| `app/Http/Requests/StoreVacancyRequest.php` | Only form-request class; other controllers mostly call `$request->validate([...])` inline |

### Frontend structure

| Path | Purpose |
|------|---------|
| `resources/js/app.js` | Inertia bootstrap; Pinia; global `ProgressBar`/`ToastContainer`/`ConfirmDialog`; `useSessionExpiry()`/`useIdleTimer()` composables run globally; axios base URL is rewritten to `window.location.origin` to survive subpath/proxy deployments; `inertia:error` listener toasts on 419/403 |
| `resources/js/Pages/` | Full-page components, grouped by area (see above) |
| `resources/js/Layouts/` | `PublicLayout.vue`, `ApplicantLayout.vue`, `AdminLayout.vue`, `HrmpsbLayout.vue`, `AppointingAuthorityLayout.vue` |
| `resources/js/services/api.js` | Axios instance; attaches `Bearer` token from `localStorage['auth_token']`; on 401 clears auth and redirects to `/login?next=...`; `notifyConflict()` (also hooked onto the global axios in `app.js`) toasts the reason for a `conflict_of_interest` 403 |
| `resources/js/Components/UI/WorkspaceSwitcher.vue` | Loading card for switching workspace; targets `hrmpsb`, `admin`, `applicant`. Admin/HRMPSB sidebars have "My Applications", the applicant sidebar has "Back to … Workspace" for staff |
| `resources/js/Pages/Admin/Vacancies/RecusalsModal.vue` | Per-vacancy recusals (declare a relative/other conflict, see automatic ones) and the board-size warning |

Tailwind v4 is used via `@tailwindcss/vite`. Build-time CSS warnings about unknown `@theme`/`@plugin`/`@source` rules from the LightningCSS minifier are harmless.

### Authentication & authorization

API auth uses Laravel Sanctum (bearer tokens). The token is stored in `localStorage` and attached via the axios interceptor in `api.js`.

**Roles** (`users.role` enum) — collapsed by migration `2026_06_27_100000_simplify_users_role_to_three.php` down to exactly three values: `applicant`, `hrmpsb`, `admin`. (Older values like `hr-officer`/`hr-manager`/`hrmpsb-member`/`appointing-authority` existed transiently and were migrated into `admin`/`hrmpsb` — don't expect to see them in current data.) `UserController::ALLOWED_ROLES` enforces this same set on create/update. **A user can be both staff and an applicant**: `ApplicationController::store` accepts any of the three roles (a complete profile with documents is still required), and a staff member who applies is recused from that vacancy (see "Conflict of interest"). `applications.is_internal` snapshots whether the applicant was IMIS-linked when they applied; it is for reporting only. **Every vacancy is open to internal and external applicants alike** — do not add an internal-only restriction.

**Finer-grained HRMPSB roles live outside `users.role`**, in the `hrmpsb_compositions` table (model `HrmpsbComposition`), which is now global rather than per-vacancy. `HrmpsbComposition::ROLES` includes `chairperson`, `secretariat`, `appointing-authority`, `hr-chief`, `pintig-representative-1st`, `pintig-representative-2nd`, plus a dynamically-resolved `head-of-unit` role per vacancy's place of assignment (resolved by `HrmpsbCompositionService`, excluding HRD/ORD). The former `director-representative`/`division-chief-representative` static roles were retired in favor of this dynamic Head of Unit designation.

**`User::canAccessAdminModule()`** (app/Models/User.php) is the crux of the whole authorization scheme — true if `role === 'admin'`, or if `role === 'hrmpsb'` *and* the user holds an active `secretariat`/`hr-chief` designation (`hasAdminDesignation()`). Almost every policy and the `admin-access` middleware defer to it.

Authorization is enforced in three layers, all on the API side:
1. **Route middleware** (`routes/api.php`): `auth:sanctum` (any authenticated user), `admin-access` (admin module), `role:hrmpsb,admin` (the bulk of pipeline-stage endpoints).
2. **Policies/Gates** (registered in `AppServiceProvider::boot()`): `VacancyPolicy` (delete is admin-only, not just admin-module), `ApplicationPolicy` (owners can view their own application via `applicantProfile->id`), `DocumentPolicy` (verify), and gate-based `EvaluationPolicy` (`evaluate-application`, `lock-evaluation`, `unmask-identities` — driven by active `hrmpsb_compositions` rows, since access depends on board membership, not ownership).
3. **`pipeline-stage:<key>` middleware** on `hrmpsb/*` web routes — gates workflow *progression* (has the prerequisite stage been locked/completed?), independent of role.

Password reset requires: min 8 chars, at least one uppercase, one lowercase, one number (`Password::min(8)->letters()->mixedCase()->numbers()`). Non-remember logins expire after 2 hours; remember-me and Google OAuth tokens never expire. Google OAuth is wired via Socialite but `GOOGLE_CLIENT_ID`/`SECRET`/`REDIRECT_URI` are empty in `.env` by default.

### IMIS integration

The portal sits under the IMIS hub (sibling repo `htdocs/new-imis`, GitHub `imis_v2`). Config is in `config/services.php` → `imis` (`IMIS_SSO_URL`, `IMIS_SSO_AUDIENCE`, `IMIS_ASSET_ORIGIN`, `IMIS_API_URL`, `IMIS_API_TOKEN`; the token must equal IMIS's `RECRUITMENT_API_TOKEN`; unset = off).

- **Sign-in** (`ImisSsoController`, page `Auth/ImisSso.vue`): IMIS sends the browser to `/sso` with a one-time token, which is redeemed server-to-server. IMIS's `role_code` maps: `admin`→`admin`, `hrmpsb`→`hrmpsb`, `employee` **or no code (guest launch)**→`applicant` (an internal applicant). `applicant` and unknown codes are refused. The account is linked by `imis_user_id` (else once by email), and its name/email/role are rewritten from IMIS on every sign-in. Linked accounts have no password (`User::isImisManaged()`). By email: a staff code never claims an applicant account (409); an employee code may link an applicant account only if its email is verified, and never takes over a staff account. Sign-out from IMIS revokes the token via `imis_sid_hash` (`ImisSsoLogoutController`).
- **IMIS side**: "Online Recruitment System" must **not** be marked Restricted in IMIS, otherwise employees without a role see "No Access" and never reach the portal.
- **Vacancies from IMIS plantilla** (`ImisPlantilla`, `Vacancy.imis_plantilla_item_id`); the Appointing Authority's selection is written back by `ReportSelectionToImis`.
- **Profile import** (`POST /api/profile/import-imis`, `ProfileController::importFromImis`, button on `Applicant/CompleteProfile.vue` for linked users): `ImisProfileImporter` pulls the *verified* PDS (latest verified submission), approved trainings, and the PDS/TOR/IPCR from the 201 file via IMIS's `api/v1/recruitment/employees/{imisUserId}/{pds|documents|documents/{id}/file}`. **Add-only**: filled fields are kept, existing rows aren't duplicated, a filled document slot isn't replaced. Documents are copied (not linked), must be PDFs, and are checked against IMIS's SHA-256. It writes only to columns defined by migrations. Not imported: region, work duties/accomplishments, application letter, COE (no 201 equivalent). `applicant_profiles.imis_imported_at` records the last run.

### Conflict of interest

Anyone with their own application on a vacancy, or a `vacancy_recusals` row for it, takes no part in evaluating it (QS, exams, BEI, CBWE, EOPT, background, deliberation, decisions, CS forms), whatever their role. Enforced by the `no-conflict` middleware (withdrawn applications still count). `vacancy_recusals` (model `VacancyRecusal`; reasons `applicant` — automatic and not removable, `relative`, `other`) is managed by `VacancyRecusalController` at `/api/admin/vacancies/{vacancy}/recusals` (admin module). Recused members drop out of `HrmpsbCompositionService::forVacancy()` for that vacancy only. `quorumStatus()` warns, without blocking, when principals left < `config/hrmpsb.php` `min_members` (`HRMPSB_MIN_MEMBERS`, **placeholder 3** until HR confirms the real number; 0 turns it off). The relative/degree rule itself can't be detected by the system — admins record it.

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
| `HrmpsbComposition` | Assigns a `User` to a global HRMPSB role (see roles above) |
| `VacancyRecusal` | A person recused from one vacancy (applied, relative, other); see "Conflict of interest" |

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

MySQL database (local: `csc_recruitment`, root/no password under XAMPP). Session, cache, and queue all use the `database` driver. Migrations are additive — check migration dates when something looks contradictory; the `role` enum, `vacancies.status`, and the name-column layout have each gone through multiple superseding migrations, so trust the model + latest migration over older ones. **Schema drift:** the local MySQL `applicant_profiles` has ~40 extra columns (address parts `res_*`/`perm_*`, government IDs, `place_of_birth`, `height_m`, `pds_generated_*`, …) and `civil_service_eligibilities` exists, but no migration in this repo creates them, so fresh installs and the SQLite test database lack them and no model uses them. Don't write to them without adding a migration.

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
