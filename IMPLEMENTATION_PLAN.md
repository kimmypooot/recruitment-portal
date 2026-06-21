# CSC RO VIII Recruitment System — Modernization & Compliance Implementation Plan

> Based on PROCESS.md requirements, 2025 ORA-OHRA alignment, and real codebase analysis.
> Approach: Incremental enhancement of the existing Laravel 13 + Vue 3 + Inertia system. No full rewrite.

---

## Context

The system is live at CSC Regional Office VIII and has working foundations: Auth (Sanctum), Vacancy CRUD, Application submission, Applicant profile, and a basic Admin dashboard. However, it does not yet support the full CSC Recruitment, Selection, and Placement workflow — specifically HRMPSB governance, blind evaluation, multi-evaluator scoring, CSC form generation, or compliance deadline tracking. This plan extends the existing system to close those gaps without disrupting production.

---

## 1. Executive Summary

The current system covers approximately 30% of the required CSC recruitment workflow. The critical gaps are:
- No HRMPSB role structure (dynamic, configurable RBAC)
- No qualification screening (QS) evaluation matrix
- No exam scoring (TWE/CBWE) or BEI rating system
- No anonymization / blind evaluation
- No CS Form generation (33-A, 33-B, CS Form 1 Revised 2025)
- No compliance deadline tracking (10-day publication, 30-day submission)
- Authorization policies are referenced but not defined — the system will throw errors the moment publish/archive/delete is triggered
- Several notification classes have runtime errors

**Direction:** Four phased increments over ~20 weeks. Each phase delivers working, production-safe features. No phase breaks existing functionality.

---

## 2. Current System Assessment

### What exists and works
| Module | Status |
|--------|--------|
| Sanctum token auth + login/register | ✓ Working |
| Vacancy listing (public, paginated, filtered) | ✓ Working |
| Vacancy CRUD (draft/publish/archive) | ⚠ Broken — policies missing, authorize() will throw |
| Application submission by applicant | ✓ Working (no role guard) |
| Applicant profile completion | ✓ Working |
| Document upload (profile + application) | ✓ Working |
| Admin dashboard (stats) | ✓ Working (cached 1hr) |
| HR application status updates | ✓ Working (no role guard) |
| Audit log (vacancy actions only) | ⚠ Partial |
| Exam/Interview scheduling | ⚠ Stub only — no validation, no auth |
| Notifications (5 classes) | ⚠ 1 has runtime error; others are placeholder |
| Google OAuth | ⚠ Wired but credentials empty |
| Reports | ✗ Placeholder only |

### What does not exist
- HRMPSB composition management
- QS evaluation matrix (per-evaluator, per-applicant)
- TWE / CBWE score encoding
- BEI rating system
- Anonymized/blind evaluation
- Final deliberation and identity unmasking
- CS Form 33-A, 33-B, CS Form 1 (Revised 2025)
- 10-day vacancy period enforcement
- 30-day appointment submission deadline tracker
- Role-based middleware (roles exist in DB but not enforced at route level)
- Authorization Policy classes

---

## 3. Database Analysis

### Existing schema strengths
- Clean normalized base: users → applicant_profiles → applications → documents
- Soft deletes on vacancies and applications
- Separate tables for work experience, education, training
- Laravel notifications table already provisioned

### Existing schema problems
| Issue | Impact | Fix |
|-------|--------|-----|
| `applicant_profiles` has both `birthday` and `birthdate` columns | Data inconsistency | Migrate to `birthday` only, drop `birthdate` |
| `applications.applicant_id` → `applicant_profiles` (not `users`) | Confusing FK, notification code breaks | Document and enforce; fix notification |
| Application status enum in model (`submitted/under_review/.../withdrawn`) doesn't match controller (`screened/qualified/.../appointed`) | Status tracking unreliable | Standardize enum, create migration |
| No HRMPSB tables | Entire governance layer missing | Add in Phase 2 |
| No evaluation/scoring tables | Cannot support QS/TWE/BEI | Add in Phase 2–3 |
| `exam_schedules` / `interview_schedules` have no evaluator FK | Scheduling exists but not linked to HRMPSB members | Extend in Phase 2 |

### New tables needed (with migration order)

```
Phase 1 fixes:
  - ALTER applicant_profiles: drop birthdate, standardize status enum

Phase 2 adds:
  - hrmpsb_compositions (id, vacancy_id, user_id, hrmpsb_role, member_type: principal|alternate, is_active, timestamps)
  - qs_evaluations (id, application_id, evaluator_id, education_score, experience_score, training_score, eligibility_score, total_score, remarks, locked_at, evaluated_at, timestamps)
  - compliance_deadlines (id, vacancy_id, deadline_type, due_at, completed_at, notified_at, timestamps)

Phase 3 adds:
  - exam_results (id, application_id, exam_type: TWE|CBWE, raw_score, max_score, encoded_by, encoded_at, timestamps)
  - bei_ratings (id, application_id, evaluator_id, competency_scores JSON, total_rating, remarks, rated_at, locked_at, timestamps)
  - anonymization_tokens (id, application_id, token, unmasked_at, unmasked_by, timestamps)

Phase 4 adds:
  - cs_forms (id, application_id, form_type: 33A|33B|form1, generated_at, signed_at, submitted_to_csc_at, file_path, timestamps)
  - submission_tracking (id, vacancy_id, form_type, due_at, submitted_at, status, timestamps)
```

---

## 4. Application Architecture Review

### Backend
- **Controllers** — 9 controllers; only `VacancyController` uses a FormRequest. All others call `$request->validated()` without rules (returns empty array silently). Every controller needs validation added.
- **Authorization** — `VacancyController` calls `$this->authorize()` but no Policy classes exist. Must create `VacancyPolicy`, `ApplicationPolicy`, `DocumentPolicy`.
- **Middleware** — No role-checking middleware registered. `bootstrap/app.php` has an empty `withMiddleware()` closure. A `EnsureRole` middleware needs to be created and registered.
- **Services** — Only `AuditLog` exists. Need `HrmbsboardService`, `EvaluationService`, `AnonymizationService`, `FormGeneratorService`.
- **Notifications** — `ApplicationStatusUpdated` accesses `$this->application->applicant->full_name` but `applicant` resolves to `ApplicantProfile` which has no `full_name`. Fix: use `first_name . ' ' . last_name`.

### Frontend
- **Auth** — Sanctum Bearer token stored in `localStorage`, injected via axios interceptor in `api.js`. No Pinia store yet (installed but unused).
- **Pages** — 14 pages; most admin pages are thin shells with no real functionality yet.
- **Components** — Only 4 shared components. The evaluation matrix, BEI forms, and HRMPSB management UI will need significant new page/component work.
- **State management** — Pinia is installed. Use it for HRMPSB session state, evaluation draft state, and user role context.

### Installed but unused (leverage in later phases)
- `maatwebsite/excel` → Reports export (Phase 4)
- `spatie/laravel-backup` → Schedule in deployment config (Phase 1)
- `cropperjs` → Profile photo cropping (Phase 2)
- `pinia` → HRMPSB evaluation state (Phase 3)

---

## 5. Compliance Gap Analysis

| Requirement | Current State | Gap |
|-------------|--------------|-----|
| 10-day vacancy publication period | Deadline field exists; not enforced | No auto-close, no countdown, no compliance alert |
| Completeness check before acceptance | `isComplete()` checks 6 fields | Needs to gate application submission |
| Multi-evaluator QS input | None | Full QS matrix missing |
| Per-member scoring visibility (controlled) | None | Needs HRMPSB role gate |
| QS result lock before progression | None | Lock mechanism missing |
| Notification of qualified applicants | Partial (email only stub) | No SMS, no scheduling link |
| TWE/CBWE score encoding | Stub controller only | No validation, no HRMPSB auth |
| BEI rating by HRMPSB members | None | Missing |
| Blind evaluation until deliberation | None | No anonymization token system |
| Identity unmasking at deliberation | None | Missing |
| HRMPSB configurability without code | None | No composition management UI |
| Principal/alternate switching | None | Missing |
| All HRMPSB actions auditable | Partial (vacancy only) | Must extend AuditLog to all evaluations |
| CS Form 33-A generation | None | Missing |
| CS Form 33-B generation | None | Missing |
| CS Form 1 (Revised 2025) | None | Missing |
| PNPKI digital signature | None | Phase 4 |
| 30-day submission deadline tracking | None | Missing |
| Data Privacy Act compliance | Partial (privacy modal exists) | No data access log, no purpose limitation |
| Merit Selection Plan alignment | None documented | Needs workflow gate |

---

## 6. Risk Assessment

| Risk | Severity | Likelihood | Mitigation |
|------|----------|-----------|------------|
| No authorization policies → any authenticated user can modify any record | Critical | Certain (next publish click will throw) | Phase 1, Day 1 fix |
| Notification runtime error on status update | Critical | Certain when HR changes status | Fix `full_name` in Phase 1 |
| Application status enum mismatch | High | Certain when HR uses extended statuses | Standardize in Phase 1 |
| Missing role enforcement allows HR to access admin routes and vice versa | High | Exploitable now | Phase 1 middleware |
| Fragmented migrations make `fresh --seed` unreliable | Medium | Occasional | Phase 1 cleanup |
| Anonymization bypass if token not enforced | High | Phase 3 concern | Design token gate carefully |
| CS Form errors in production could delay appointments | High | Phase 4 | Thorough testing environment |
| Queue worker stops → notifications silently dropped | Medium | Operational | Add queue monitoring in Phase 1 |

---

## 7. System Enhancement Plan (Overview)

Enhancements are incremental. Each phase produces deployable, production-safe output.

**Phase 1** — Stabilize existing system (Weeks 1–3) ✅ IN PROGRESS
**Phase 2** — HRMPSB + QS workflow (Weeks 4–8)
**Phase 3** — Evaluation, BEI, anonymization (Weeks 9–14)
**Phase 4** — CS Forms, compliance tracking, digital signatures (Weeks 15–20)

---

## 8. RBAC + HRMPSB Governance Model

### Extended role set (add to `users.role` enum)
```
applicant | hr-officer | hr-manager | admin (existing)
hrmpsb-chairperson | hrmpsb-member | hrmpsb-secretariat | appointing-authority (new)
```

### HRMPSB composition model
- `hrmpsb_compositions` table links `vacancy_id → user_id` with `hrmpsb_role` and `member_type` (principal/alternate)
- Admin assigns HRMPSB members per vacancy cycle through a dedicated UI
- Switching principal ↔ alternate is an admin action, fully audited
- A user can hold different HRMPSB roles across different vacancies

### Permission enforcement layers
1. **Route level** — `EnsureRole` middleware registered in `bootstrap/app.php` using `withMiddleware()`
2. **Policy level** — Laravel policies for each model enforced via `authorize()` calls
3. **Frontend level** — Role passed via Inertia shared props; navigation and action buttons conditioned on role

### Policy classes needed
```
app/Policies/VacancyPolicy.php
app/Policies/ApplicationPolicy.php
app/Policies/DocumentPolicy.php
app/Policies/EvaluationPolicy.php (Phase 2)
app/Policies/HrmbsboardPolicy.php (Phase 2)
```

---

## 9. Database Improvement Roadmap

### Phase 1 migrations
```php
// Fix 1: Drop duplicate birthdate column
Schema::table('applicant_profiles', function (Blueprint $table) {
    $table->dropColumn('birthdate'); // keep 'birthday'
});

// Fix 2: Standardize application status enum
// Canonical values:
// submitted | under_review | screened | qualified | disqualified |
// exam_scheduled | interviewed | shortlisted | for_interview |
// recommended | appointed | completed | withdrawn
```

### Phase 2 migrations
```php
hrmpsb_compositions: vacancy_id (FK), user_id (FK), hrmpsb_role (string), member_type (enum: principal|alternate), is_active (boolean), assigned_at, assigned_by
qs_evaluations: application_id (FK), evaluator_id (FK→users), education_score, experience_score, training_score, eligibility_score, total_score (computed), remarks, evaluated_at, locked_at
compliance_deadlines: vacancy_id (FK), deadline_type (string), due_at, completed_at, notified_at
```

### Phase 3 migrations
```php
exam_results: application_id (FK), exam_type (enum: TWE|CBWE), raw_score (decimal), max_score (decimal), encoded_by (FK→users), encoded_at
bei_ratings: application_id (FK), evaluator_id (FK→users), competency_scores (JSON), total_rating (decimal), remarks (text), rated_at, locked_at
anonymization_tokens: application_id (FK unique), token (string unique), unmasked_at, unmasked_by (FK→users nullable)
```

### Phase 4 migrations
```php
cs_forms: application_id (FK), form_type (enum: 33A|33B|form1), file_path, generated_at, signed_at, submitted_to_csc_at
submission_tracking: vacancy_id (FK), deadline_type, due_at, submitted_at, status
```

---

## 10. Workflow Optimization Design

### End-to-end lifecycle (system-enforced transitions)

```
[Vacancy Published] → 10-day deadline set in compliance_deadlines
    ↓ auto-close on deadline
[Applications Received] → profile completeness gate before submission accepted
    ↓
[QS Evaluation] → each HRMPSB member fills qs_evaluations row
    → Secretary sees aggregated view; individual scores visible only to evaluator + secretariat
    → Secretary locks QS list → no further edits
    ↓
[Notify Qualified] → email sent; exam schedule created
    ↓
[TWE / CBWE] → Secretariat encodes scores into exam_results
    → Rankings computed automatically
    ↓
[BEI] → HRMPSB members encode bei_ratings (anonymized applicant codes shown)
    → All ratings locked before proceeding
    ↓
[Final Deliberation] → identity unmasking: anonymization_tokens.unmasked_at set
    → Consolidated ranking view (QS + Exam + BEI)
    → HRMPSB endorses top candidates
    ↓
[Appointing Authority Selection] → appointing-authority role selects appointee
    ↓
[CS Form Generation] → 33-A generated; 33-B if applicable
    → PNPKI signature slot
[CS Form 1 Generation] → submitted within 30 days; deadline tracked in submission_tracking
```

### Application status lifecycle (enforced transitions)
```
submitted → under_review → screened → [qualified | disqualified]
qualified → exam_scheduled → interviewed (BEI) → shortlisted → recommended → appointed → completed
Any state → withdrawn (applicant-initiated)
```

---

## 11. Anonymization & Anti-Bias Framework

### Design
- On QS list lock, generate a unique `anonymization_token` for each application
- Evaluators (QS, TWE/CBWE, BEI) see only the token code, not applicant name/photo
- Token → identity mapping is stored in `anonymization_tokens` but only readable by `admin` and `appointing-authority` after deliberation lock
- At final deliberation, secretary triggers "unmask" — sets `unmasked_at` timestamp
- All unmask events are audit-logged with who triggered and when

### Implementation components
- `app/Services/AnonymizationService.php` — `generateToken(Application)`, `unmask(Application, User)`, `getAnonymizedView(Application)`
- Backend: Evaluation API responses strip name/photo until `unmasked_at` is set
- `EvaluationPolicy::view()` — returns anonymized data unless current user has unmask permission

---

## 12. Audit Trail & Security Framework

### Expand AuditLog coverage
Currently only vacancy actions are logged. Extend `AuditLog::record()` calls to:
- All application status changes (with before/after values)
- All HRMPSB composition changes (assign, remove, switch principal/alternate)
- All QS evaluation submissions and locks
- All exam score encodings
- All BEI rating submissions and locks
- All identity unmask events
- All CS form generations and submissions
- All user role changes

### Security hardening (Phase 1)
- Create all missing Policy classes
- Register `EnsureRole` middleware
- Add validation rules to `ExaminationController`, `InterviewController`, `UserController`
- Gate `UserController` behind `admin` role

### DPA compliance additions (Phase 2+)
- Log all profile data accesses (view events in audit_logs)
- Ensure document serving endpoints log access
- Add data retention policy enforcement via `spatie/laravel-backup`

---

## 13. Forms & Reporting Module Design

### CS Form generation (Phase 4)
Use `barryvdh/laravel-dompdf` to generate PDFs server-side.

| Form | Content | Trigger |
|------|---------|---------|
| CS Form 33-A | Appointment details, position, salary grade, effectivity | HR Manager after appointee selected |
| CS Form 33-B | Additional appointment info (if applicable) | Same trigger |
| CS Form 1 (Revised 2025) | Personal Data Sheet extract | On appointment issuance |

### Reports (Phase 4)
Use installed `maatwebsite/excel` for:
- Qualified applicants list (Excel)
- Comparative assessment matrix (Excel)
- Appointment report (Excel)
- Pipeline summary dashboard

---

## 14. Implementation Phases

### Phase 1 — Compliance-Critical Fixes (Weeks 1–3) ✅ IN PROGRESS

**Goal:** Make the existing system safe and production-stable.

**Deliverables:**
- `app/Policies/VacancyPolicy.php` ✅
- `app/Policies/ApplicationPolicy.php` ✅
- `app/Policies/DocumentPolicy.php` ✅
- `app/Http/Middleware/EnsureRole.php` ✅
- `bootstrap/app.php` — middleware registered ✅
- `app/Notifications/ApplicationStatusUpdated.php` — full_name fixed ✅
- `app/Http/Controllers/ExaminationController.php` — validation added ✅
- `app/Http/Controllers/InterviewController.php` — validation added ✅
- `app/Http/Controllers/UserController.php` — role guard + validation ✅
- `app/Http/Controllers/ApplicationController.php` — profile ID fix, notification dispatch, audit log ✅
- `database/migrations/..._fix_applicant_profiles_birthdate.php` ✅
- `database/migrations/..._standardize_application_status.php` ✅
- `database/factories/UserFactory.php` — role states ✅
- `routes/api.php` — role middleware applied ✅
- `app/Providers/AppServiceProvider.php` — policies registered ✅

---

### Phase 2 — HRMPSB + QS Workflow (Weeks 4–8)

**Goal:** Full HRMPSB configurability and qualification screening matrix.

**Deliverables:**
- `database/migrations/..._create_hrmpsb_compositions_table.php`
- `database/migrations/..._create_qs_evaluations_table.php`
- `database/migrations/..._create_compliance_deadlines_table.php`
- `app/Models/HrmbsboardComposition.php`
- `app/Models/QsEvaluation.php`
- `app/Models/ComplianceDeadline.php`
- `app/Http/Controllers/HrmbsboardController.php`
- `app/Http/Controllers/QsEvaluationController.php`
- `app/Console/Commands/CloseExpiredVacancies.php`
- `resources/js/Pages/Admin/Hrmpsb.vue`
- `resources/js/Pages/Hrmpsb/QsEvaluation.vue`
- Route additions (web + api)

---

### Phase 3 — Evaluation, BEI, Anonymization (Weeks 9–14)

**Goal:** Complete evaluation pipeline with blind scoring.

**Deliverables:**
- `database/migrations/..._create_anonymization_tokens_table.php`
- `database/migrations/..._create_exam_results_table.php`
- `database/migrations/..._create_bei_ratings_table.php`
- `app/Services/AnonymizationService.php`
- `app/Http/Controllers/ExamResultController.php`
- `app/Http/Controllers/BeiRatingController.php`
- `app/Http/Controllers/DeliberationController.php`
- `app/Policies/EvaluationPolicy.php`
- `resources/js/Pages/Hrmpsb/BeiRating.vue`
- `resources/js/Pages/Hrmpsb/Deliberation.vue`
- `resources/js/Pages/Hrmpsb/ExamResults.vue`
- `resources/js/stores/evaluation.js`
- `resources/js/stores/auth.js`

---

### Phase 4 — CS Forms, Compliance Tracking, Digital Signatures (Weeks 15–20)

**Goal:** Document generation, CSC submission tracking, PNPKI readiness.

**Deliverables:**
- `composer.json` (add barryvdh/laravel-dompdf)
- `database/migrations/..._create_cs_forms_table.php`
- `database/migrations/..._create_submission_tracking_table.php`
- `app/Services/FormGeneratorService.php`
- `app/Services/PnpkiService.php`
- `app/Http/Controllers/ReportController.php` (implement)
- `app/Console/Commands/AlertOverdueSubmissions.php`
- `resources/views/forms/cs-form-33a.blade.php`
- `resources/views/forms/cs-form-33b.blade.php`
- `resources/views/forms/cs-form-1-2025.blade.php`
- `resources/js/Pages/Admin/ComplianceDashboard.vue`

---

## 15. Priority Matrix

### Critical (Phase 1 — must fix before any other work)
- Missing authorization policies (system throws on vacancy actions)
- `ApplicationStatusUpdated` notification runtime error
- No role enforcement at route/middleware level
- Application status enum inconsistency
- ApplicationController using user ID instead of profile ID for applicant_id

### High (Phase 2)
- HRMPSB composition management
- QS evaluation matrix with multi-evaluator support
- 10-day vacancy publication enforcement
- Audit log expansion
- Profile completeness gate on application submission

### Medium (Phase 3)
- Anonymization / blind evaluation token system
- TWE/CBWE score encoding
- BEI rating system
- Final deliberation + identity unmasking
- Pinia state management for evaluation drafts

### Low (Phase 4)
- CS Form 33-A / 33-B / Form 1 PDF generation
- PNPKI digital signature stub
- 30-day submission deadline tracking
- Reports and Excel exports
- SMS notifications (if SMS gateway is available)

---

## 16. Deployment Strategy

### Before each phase deployment
```bash
php artisan backup:run --only-db
php artisan down --retry=60
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
php artisan up
```

### Rollback procedure
```bash
php artisan migrate:rollback --step=[N]
php artisan queue:restart
php artisan optimize:clear
```

### Phase gates
- Phase 1 → Pass: all tests pass; vacancy publish/archive works; HR status update sends notification without error; GET /api/admin/users returns 403 for non-admin
- Phase 2 → Pass: admin can assign HRMPSB members; QS matrix saves and aggregates; vacancy auto-closes at deadline
- Phase 3 → Pass: evaluators see anonymized tokens; unmask only accessible to authorized roles; consolidated deliberation view correct
- Phase 4 → Pass: CS Forms generate valid PDFs; 30-day deadline tracker alerts HR; reports export complete data
