# Four items held back from the UX audit fix pass

Companion to the UI/UX audit + fix pass on Login/Public/Applicant Dashboard. These need product decisions or new backend surface, not just a code change — this is the plan for each, so we can decide what to build and in what order.

**Status: planning only — nothing below is built yet.**

## At a glance

| Feature | Smallest useful version | Effort | Touches DB? |
|---|---|---|---|
| Application status lookup | Reference-code + email form, read-only status page | 1–2 days | Yes — new column |
| Draft autosave | Extend existing mechanism to the 3 record modals | 0.5–1 day | No |
| Saved vacancies | Client-only, Pinia + localStorage, no login required | ~1 day | No (v1) |
| Role-check caching | Optimistic render from cached role, reconcile in background | ~0.5 day | No |

---

## Passwordless application-status lookup

**Effort: 1–2 days**

Right now, checking your status requires a full account and login. Every other government recruitment portal I'm aware of offers a no-login lookup — it's usually the single biggest lever on "how do I check my status" support volume.

### What it is

A public page where an applicant enters a **reference code** (given at submission) plus their **email**, and sees the application's current status and timeline — nothing else, no full profile.

### Data model change

- Migration: add `reference_code` (short, unique, e.g. 10-char alphanumeric) to `applications` — `database/migrations/2026_06_19_132000_create_applications_table.php` is the existing table to extend.
- Backfill: a one-off command generates codes for existing rows so nothing already submitted is left untrackable.
- Generate the code at submission time in `ApplicationController::store()`.

### Backend

- New public, rate-limited route — same pattern as `routes/api.php:46`'s login throttle (`throttle:5,1`) — e.g. `POST /api/track`.
- Looks up by `reference_code`, cross-checks the submitted email against the linked user, and returns only status + status timestamps + vacancy title. No documents, no personal data beyond what the visitor already typed in.

### Frontend

- New page, e.g. `Pages/Public/TrackApplication.vue`, using `PublicLayout`.
- The footer link "Track My Application" already exists in `PublicLayout.vue` — it currently points at `/login`. Repointing it here is a one-line change once the page exists.
- Email the reference code at submission (existing notification classes in `app/Notifications/` are the pattern to follow) so it isn't only visible while logged in.

### Risk — enumeration

A guessable code plus no email check would let someone fish for other people's application statuses. Mitigate with a code long enough to not be brute-forceable inside the rate limit window, and always requiring the email match, not the code alone.

### Open question

Reference code only, or code + email + last name? More friction at lookup time trades off against a stronger identity check — worth a quick call before building.

---

## Full profile draft-autosave

**Effort: 0.5–1 day**

`CompleteProfile.vue` already autosaves the Personal Info tab to localStorage — this extends the same idea to the two places that still lose work: the Experience/Education/Training entry modals, and in-progress document selections.

### What exists today

A debounced (800ms) draft save keyed per-user, scoped to the `personal` object only — `CompleteProfile.vue` (`DRAFT_KEY`, `saveDraft`, `restoreDraft`). It's the right pattern, just narrow in scope.

### What to extend

- Generalize the existing save/restore logic into a small composable — `useDraft(key, dataRef, { debounceMs })` — so it isn't hand-copied a second and third time.
- Apply it to the three record modals' local form state (experience, education, training) while they're open, so closing a modal by accident or hitting a session timeout mid-entry doesn't erase what was typed.
- Clear the relevant draft immediately after a successful server save — same as the existing personal-tab behavior — so a stale draft never overwrites freshly-saved data on next visit.

### Explicitly out of scope: in-progress document uploads

A selected `File` object can't be serialized into localStorage. The existing tab-switch guard (`hasDocChanges`) that warns before navigating away with an unsaved file already covers this reasonably — no further autosave is possible here without switching to something like IndexedDB, which isn't worth the complexity for this.

---

## Saved / shortlisted vacancies

**Effort: ~1 day (v1)**

Applicants comparing several postings currently have to keep re-searching. A "save for later" star turns the vacancy grid into something they can come back to.

### v1 — client-only, ship this first (recommended start)

- **Pinia** is already an installed dependency and completely unused elsewhere in the app — this is the natural first real use for it.
- A `useSavedVacancies` store, persisted to localStorage, holds a set of vacancy IDs. No login required, works for guests too.
- A bookmark toggle icon on `VacancyCard.vue` and in `VacancyDetailModal.vue`.
- A "Saved" filter alongside the existing search/salary-grade/sort controls on both `Home.vue` and the Applicant Dashboard's vacancy browser.

### v2 — server-synced (only if there's real demand)

- A `saved_vacancies` pivot table (`user_id`, `vacancy_id`), a small controller, and a sync-on-login step that merges the local list into the account.
- Deliberately deferred — building the synced version before knowing whether people actually use the save feature at all would be solving a problem that might not exist.

### Known limitation of v1

Saved vacancies don't follow the user across devices or browsers, since nothing is stored server-side yet. Worth saying so in the UI copy rather than letting people discover it the hard way.

---

## Cache the role-check to remove the auth-flash

**Effort: ~0.5 day**

This is a refactor of two existing files, not a new feature — the goal is removing a network round-trip that currently blocks first paint on the two highest-traffic surfaces in the app.

### The current cost

- `AdminLayout.vue` re-derives the signed-in user's role asynchronously on every page mount before rendering anything — a blank-spinner flash on every single navigation, not just first login.
- `Home.vue` silently checks `/api/profile` (and, for board members, a second endpoint) on every visit to decide whether to redirect an already-logged-in visitor — blocking the whole homepage behind that check.

### The fix

- Login already returns `user.role` and stores it in `auth_user` in localStorage (`Login.vue`). That's already a usable cache — it just isn't being read synchronously anywhere.
- Read the cached role first and render optimistically; only fall back to the network call when the cache is missing or past a short TTL (e.g. 5 minutes).
- Always reconcile with a background API call after the optimistic render, so a role changed server-side (e.g. an admin demoted mid-session) still gets picked up — just not on the critical path to first paint.

### Risk

A short window where a just-revoked role still renders the old UI. The 5-minute TTL plus background reconciliation bounds this to something reasonable — a full real-time push would be over-engineering for what this portal needs.

---

## Suggested order

None of the four items above have been implemented — this is a plan to work from, not a changelog. Recommended order if picking one to start:

1. **Role-check caching** first — smallest, no product decision needed.
2. **Draft-autosave** — small, extends existing code.
3. **Saved vacancies v1** — self-contained, no backend.
4. **Status lookup** — needs the email-matching question answered first.
