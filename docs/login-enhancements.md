# Login System — Enhancement Suggestions

Below are prioritized improvements for the authentication system, grouped by impact. Each suggestion includes the rationale, the file(s) affected, and the effort level.

---

## 1. Critical Security Gaps

### 1.1 Server-side token invalidation on logout

**Problem:** Logout only clears localStorage. The Sanctum token remains valid in `personal_access_tokens` — any exfiltrated token continues to grant access indefinitely.

**Solution:** Add a `POST /api/logout` endpoint that deletes the current token via `$request->user()->currentAccessToken()->delete()`. Call it from all layouts before clearing local data.

**Files:** `AuthController.php`, `api.php` (new route), `AdminLayout.vue`, `ApplicantLayout.vue`, `HrmbsboardLayout.vue`

**Effort:** Small (1–2 hours)

### 1.2 Password reset flow

**Problem:** Completely absent. Users who forget their password have no recovery path. This is the single biggest UX gap.

**Solution:**
1. Add `password_reset_tokens` migration if it doesn't exist
2. Create `Auth/ForgotPassword.vue` and `Auth/ResetPassword.vue` page components
3. Add web routes (`/forgot-password`, `/reset-password/{token}`)
4. Add a "Forgot password?" link below the password field on `Login.vue`
5. Mail is already configured (`cscro8.imis@gmail.com` via SMTP) — no config needed

**Files:** New Vue pages, `web.php` (new routes), `AuthController.php` or a new `PasswordResetController`

**Effort:** Medium (4–6 hours)

### 1.3 Rate limiting improvements

**Problem:** 5 attempts/minute per IP is too restrictive for legitimate users who mistype their password a few times, but doesn't protect against distributed brute-force.

**Solution:** Use Laravel's `RateLimiter::for('login', ...)` in `App\Providers\AppServiceProvider` with a tiered strategy:
- 5 attempts/minute per email + IP combination
- After 5 failures: lockout for 60 seconds with a "Too many attempts. Try again in X seconds." message
- After 10 failures: 15-minute lockout

**Files:** `AppServiceProvider.php`, `api.php` (update throttle middleware)

**Effort:** Small (1 hour)

### 1.4 Differentiate error messages

**Problem:** Single "Invalid credentials." message is a security best practice but frustrating for users who can't tell if the email exists.

**Solution:** Keep the single message on the API but add client-side heuristics: after 3 failed attempts, show "Having trouble? You can reset your password." with a direct link.

**Files:** `Login.vue` only

**Effort:** Very small

---

## 2. UX & Reliability Improvements

### 2.1 Centralized route protection (server-side)

**Problem:** Inertia pages like `/admin/dashboard` render regardless of authentication — protection is entirely client-side via localStorage checks. A user who bookmarks `/applicant/dashboard` gets a broken page after their session expires.

**Solution:** Add Inertia middleware share to include the authenticated user, then guard routes server-side by checking auth before rendering the Inertia page. Use Laravel's `auth` middleware on web routes that require login.

**Files:** `HandleInertiaRequests.php` (share `auth.user`), `web.php` (add `->middleware('auth')` to protected routes), `auth.js` store (read from server-shared data)

**Effort:** Medium (3–4 hours)

### 2.2 "Remember me" functionality

**Problem:** `Auth::attempt()` is called without the `remember` parameter. Users are logged out when the browser session ends.

**Solution:**
1. Add a "Remember me" checkbox to `Login.vue`
2. Pass it to `Auth::attempt($credentials, $request->boolean('remember'))`
3. No additional work needed — Laravel handles the `remember_token` column natively

**Files:** `Login.vue`, `AuthController.php`

**Effort:** Small (1 hour)

### 2.3 Inertia shared auth data

**Problem:** `HandleInertiaRequests.php` has an empty `share()` method, so the Pinia auth store's `page.props.auth.user` is never populated server-side. The auth store relies entirely on localStorage, which breaks server-rendered pages and creates inconsistency.

**Solution:** Share the authenticated user and flash messages:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
            'error'   => fn () => $request->session()->get('error'),
        ],
    ];
}
```

**Files:** `HandleInertiaRequests.php`, `auth.js` (store already expects this shape)

**Effort:** Very small (30 minutes)

### 2.4 Logout inconsistency in AdminLayout

**Problem:** `AdminLayout.vue` removes `auth_token` but does NOT remove `auth_user` from localStorage, unlike the other layouts. This leaves stale user data.

**Solution:** Add `localStorage.removeItem('auth_user')` to the admin layout's logout handler.

**Files:** `AdminLayout.vue`

**Effort:** Very small (5 minutes)

---

## 3. Optional Enhancements

### 3.1 Email verification flow

**Problem:** `email_verified_at` exists but is never enforced. Only Google OAuth users get it auto-set. Password-registered users always show as unverified.

**Solution:**
1. Implement `MustVerifyEmail` contract on `User` model
2. Add an email verification notification
3. Create a "Verify your email" interstitial page shown after registration
4. Block access to application submission until verified

**Files:** `User.php`, new `VerifyEmail.vue` page, `web.php`, mail notification

**Effort:** Medium (4–5 hours)

### 3.2 Login audit logging

**Problem:** No record of who logged in, when, or from where. For a government recruitment system, this is valuable for accountability.

**Solution:** Use `Authenticated` event from Laravel's authentication cycle to log to `audit_logs` and/or a dedicated `login_audits` table (IP, user agent, timestamp, success/failure).

**Files:** New `LoginAudit` listener, `EventServiceProvider.php`

**Effort:** Small (1–2 hours)

### 3.3 Session timeout / idle logout

**Problem:** No automatic logout after inactivity. Users who walk away from a shared computer leave their session open.

**Solution:** Add an idle timer on the frontend (track user interaction, auto-logout after X minutes of inactivity). Back the Sanctum token with an expiry.

**Files:** New composable `useIdleTimer.js`, `Login.vue`, `api.js`

**Effort:** Small (2–3 hours)

### 3.4 Multi-factor authentication (optional)

**Problem:** A recruitment system handling PII (birth dates, addresses, IDs) should consider MFA for HR and admin roles.

**Solution:** Use Laravel's built-in `Illuminate\Validation\Rules\Password` and add time-based one-time password (TOTP) via `pragmarx/google2fa-laravel` or a similar package. Scope to admin and HR roles only.

**Effort:** Large (8–12 hours)

### 3.5 Account lockout display

**Problem:** When the throttle kicks in, the user gets a generic 429 error. No feedback explains why their login isn't working.

**Solution:** Catch 429 responses in `Login.vue` and display: "Too many login attempts. Please wait X seconds before trying again."

**Files:** `Login.vue`

**Effort:** Very small (30 minutes)

---

## 4. Quick Wins (Ordered by Effort)

| # | Change | Effort |
|---|--------|--------|
| 1 | Add `removeItem('auth_user')` to `AdminLayout.vue` logout | 5 min |
| 2 | Share `auth.user` in `HandleInertiaRequests.php` | 30 min |
| 3 | Add "Forgot password?" link to `Login.vue` (even if it routes to a "coming soon" page) | 15 min |
| 4 | Add 429 (rate-limit) error handling to `Login.vue` | 30 min |
| 5 | After 3 failed login attempts, show a password-reset hint | 30 min |
| 6 | Implement `POST /api/logout` with token deletion | 1–2 hr |
| 7 | Add "Remember me" checkbox | 1 hr |
| 8 | Login audit logging via `Authenticated` event | 1–2 hr |
| 9 | Password reset flow (complete) | 4–6 hr |
| 10 | Centralized server-side route protection | 3–4 hr |
| 11 | Idle session timeout | 2–3 hr |
| 12 | Email verification | 4–5 hr |
| 13 | Multi-factor authentication (HR/admin roles) | 8–12 hr |

---

## Summary

The most impactful work items — in order — are:

1. **Password reset** — the only thing keeping a locked-out user from regaining access
2. **Server-side token invalidation (`POST /api/logout`)** — a genuine security hole
3. **Share auth data via Inertia** — foundational for consistent auth state
4. **Server-side route protection** — prevents serving broken pages to unauthenticated users
5. **Login audit logging** — accountability for a government system
