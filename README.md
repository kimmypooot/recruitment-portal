# CSC RO VIII — Recruitment Portal

An online recruitment portal for the **Civil Service Commission Regional Office VIII**, built as a Laravel + Inertia.js (Vue 3) single-page application. It implements the full HRMPSB (HR Merit Promotion and Selection Board) recruitment pipeline: applicants browse vacancies and submit applications; an HRMPSB board screens, examines, interviews, and deliberates on candidates; an Appointing Authority makes the final selection; and administrators oversee users, audit logs, and system configuration.

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.3+), Laravel Sanctum (API authentication), Laravel Socialite (Google OAuth)
- **Frontend:** Vue 3 (Composition API) + Inertia.js, Pinia for state management
- **Styling:** Tailwind CSS v4
- **Build tooling:** Vite
- **Database:** MySQL / MariaDB
- **Other notables:** DomPDF (CS Form generation), Maatwebsite Excel, Spatie Laravel Backup

## Prerequisites

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL/MariaDB (XAMPP works fine for local development)

## Getting Started

```bash
composer run setup
```

This installs PHP and JS dependencies, copies `.env.example` to `.env`, generates an application key, runs migrations, and builds frontend assets.

Then start local development (Laravel server, queue listener, and Vite HMR together):

```bash
composer run dev
```

The app serves at `http://localhost:8010` by default.

### Manual setup

If you'd rather run the steps individually:

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* and other values in .env, then:
php artisan migrate
npm install
npm run dev    # or: npm run build
```

## Configuration Notes

A few `.env` values worth knowing about beyond the Laravel defaults:

| Variable | Purpose |
|---|---|
| `TRUSTED_PROXIES` | Set to your reverse proxy's IP/CIDR (or `*`) if deployed behind one that terminates TLS — needed for HTTPS detection and secure cookies to work correctly. |
| `SESSION_SECURE_COOKIE` | Set to `true` in production (HTTPS). |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | Required only if enabling "Sign in with Google". |
| `MAIL_MAILER` | Defaults to `log` locally — set real SMTP credentials in production so notifications actually send. |
| `QUEUE_CONNECTION` | Defaults to `database` — most notifications are queued (`ShouldQueue`), so a queue worker must be running in production (`php artisan queue:work`, under a process supervisor). |

## Common Commands

```bash
# Database
php artisan migrate
php artisan migrate:fresh --seed   # wipe and reseed

# Tests
composer run test                  # full PHPUnit suite
php artisan test --filter TestName # a single test
npm run test:frontend              # Vitest (frontend unit tests)

# Code style
./vendor/bin/pint                  # Laravel Pint (PHP CS Fixer)

# Utilities
php artisan optimize:clear         # clear all caches
php artisan route:list             # inspect registered routes
php artisan tinker                 # REPL
```

## Recruitment Pipeline

Applications move through a fixed sequence of HRMPSB evaluation stages, each gating the next:

```
Pre-Assessment → QS → Written Exam (TWE) → CBWE → BEI → EOPT →
Background Check → Deliberation → Appointing Authority Decision
```

Roles are split into three account-level roles (`applicant`, `hrmpsb`, `admin`) plus finer-grained HRMPSB board designations (Chairperson, Secretariat, HR Chief, PINTIG Representatives, Appointing Authority, and a per-vacancy Head of Unit) layered on top of the `hrmpsb` role.

## Contributing

This is an internal system for CSC Regional Office VIII. If you're working on it:

- Follow the existing code conventions (see `CLAUDE.md` for a detailed architecture overview).
- Run `./vendor/bin/pint` and `composer run test` before opening a PR.
- Keep commits focused — prefer several small, well-described commits over one large one.

## License

Internal application developed for the Civil Service Commission Regional Office VIII. Not licensed for external redistribution.

## Sign-in from IMIS

HR staff and HRMPSB board members sign in through the IMIS portal (the hub);
applicants are external and keep their own sign-in and Google. IMIS sends the
browser to `/sso?sso_token=…`; the page posts the token to `POST /api/sso`
(`ImisSsoController`), which redeems it at IMIS and issues a Sanctum token.

- IMIS grants one of two roles whose **Code** is `admin` or `hrmpsb`; it
  decides who may sign in and as which role. Board designations stay in
  `hrmpsb_compositions`.
- The account is linked by `users.imis_user_id` (else once by email, never to
  an applicant), created on first sign-in, and rewritten from IMIS each time.
- Linked accounts cannot use a password or Google here; an unlinked admin keeps
  the password as the break-glass way in.
- `.env`: `IMIS_SSO_URL=<IMIS>/api/v1/sso/validate`, and `IMIS_SSO_AUDIENCE` (this
  app's host[:port] as registered in IMIS `SSO_HUB_HOSTS`, if it differs from
  `APP_URL`). `IMIS_ASSET_ORIGIN` is the origin that serves IMIS photos (shown
  as the header avatar; allowed in the Content-Security-Policy `img-src`).
