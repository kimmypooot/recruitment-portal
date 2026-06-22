# Systems Analysis Report
**CSC Regional Office Recruitment Portal — Enhancement & Gap Assessment**

**Prepared by:** Systems Analyst (Claude Code)
**Date:** 2026-06-22
**Codebase version:** branch `main` @ `904ed57`

---

## Part 1 — Critical CSC Policy Violations (Fix Immediately)

These are not feature gaps — they are active compliance violations that would expose the agency to legal risk.

---

### 1. Publication Period Is Hardcoded at 10 Days — Violates RA 7041

**Location:** `app/Http/Controllers/VacancyController.php:146`

```php
$deadline = $publishedAt->copy()->addDays(10);
```

**Policy basis:** RA 7041 (Publication Law) and CSC MC No. 22, s. 2011 require career service vacancies to be posted for a **minimum of 15 calendar days** in the agency bulletin board AND the CSC Career Employment Information System (CEIS). 10 days is legally insufficient and any appointment resulting from a sub-15-day publication can be invalidated by CSC.

**Fix:** Change `addDays(10)` to `addDays(15)` and make it a configurable constant, since some positions (e.g., for the Qualified Next-in-Rank rule) may have different periods.

---

### 2. IPCR Is Listed as Optional for All Applicants — Partially Wrong

**Location:** `resources/js/Pages/Vacancies/Apply.vue:386`

```js
{ key: 'ipcr', label: 'IPCR', required: false, ... }
```

**Policy basis:** Under CSC MC No. 3, s. 2001 and the PRIME-HRM framework, the IPCR (Individual Performance Contract and Review) is **required for all applicants who are incumbent government employees** (i.e., currently in service). For fresh graduates or private sector applicants it is truly optional. The system has no logic to distinguish between these two cases — it simply marks it optional for everyone.

---

### 3. No Authorization Policies — Any Auth User Has Admin Power

**Location:** `app/Http/Controllers/VacancyController.php:123`, `app/Http/Controllers/ApplicationController.php:77`

`$this->authorize('create', Vacancy::class)` is called but no `VacancyPolicy` or `ApplicationPolicy` exists. Laravel silently fails open when no policy is registered — the call does **not** throw; it depends on Laravel version behavior. Any registered user can currently create, publish, and delete vacancies.

---

### 4. ApplicationStatusUpdated Notification Crashes the Queue Worker

**Location:** `app/Notifications/ApplicationStatusUpdated.php:40`

```php
$positionTitle = $this->application->vacancy->position_title;
```

`$this->application->vacancy` has **no null guard**. If a vacancy is soft-deleted before the queued notification fires, this crashes, poisons the queue job, and the applicant never receives any status update emails. The fix is to eager-load vacancy in the notification constructor.

---

## Part 2 — Missing CSC-Mandated Process Steps

These are process requirements under CSC rules that the system simply has no feature for.

---

### 5. No Selection Line-Up Publication

**Policy basis:** CSC MC No. 22, s. 2011 and the Omnibus Rules on Appointments require that after QS screening, the agency must **post the selection line-up** (names of qualified candidates, position applied for, office) on the agency bulletin board for a minimum of **15 calendar days** before the Appointing Authority acts. This is a mandatory transparency step.

**What's missing:** After the HRMPSB locks QS evaluations, there is no "Publish Selection Line-Up" step, no printable selection line-up document, and no tracking of the 15-day protest window.

**Suggested feature:** After QS lock, a new button "Post Selection Line-Up" should: (a) generate a formatted PDF of the qualified candidates, (b) start a 15-day countdown tracked in `compliance_deadlines`, and (c) create a public-facing page within the portal where applicants can see the selection line-up (anonymized until the protest period closes).

---

### 6. No Protest / Appeal Mechanism

**Policy basis:** Under CSC and OMB regulations, aggrieved applicants have the right to file a protest against the selection line-up within 15 days of its posting. There is no mechanism to receive, track, or resolve protests.

**Suggested feature:** A limited protest submission form (only accessible to applicants with `submitted`/`qualified` status for the vacancy) that captures the grounds for protest and notifies the HRMPSB Secretariat. Protests should be tracked in a `protests` table with `filed_at`, `resolution`, and `resolved_at` fields.

---

### 7. No Next-in-Rank Consideration Check

**Policy basis:** CSC MC No. 22, s. 2011 mandates that for promotional appointments, employees who are **qualified next-in-rank** (QNR) to the vacant position shall be given priority consideration. The agency's Merit Selection Plan must document whether a QNR candidate exists and how they were assessed.

**What's missing:** The system has no position hierarchy data, no mapping of which positions are next-in-rank to which, and no QNR consideration tracking. When HR posts a promotional vacancy, there is no step to identify and assess internal QNR candidates first.

---

### 8. No Nepotism / Conflict of Interest Guard

**Policy basis:** Section 59, Book V of EO 292 (Administrative Code) and CSC MC No. 21, s. 2004 prohibit the appointment of relatives of the Appointing Authority or members of the HRMPSB within the 3rd degree of consanguinity or affinity.

**What's missing:** No data field capturing the relationship of applicants to the Appointing Authority, and no HRMPSB conflict-of-interest declaration step before deliberation.

**Suggested feature:** A mandatory conflict-of-interest declaration form per HRMPSB member at the start of deliberation, and a "flag for nepotism review" checkbox during QS evaluation.

---

### 9. No Internal Employee Notification

**Policy basis:** Before external publication, agencies are required to internally announce vacancies to all employees so that qualified incumbents can apply. There is currently no internal announcement mechanism separate from the public portal.

---

### 10. Top 5 Rule Not Enforced in Deliberation

**Policy basis:** Under the 2017 ORAOHRA (Omnibus Rules on Appointments and Other Human Resource Actions), only the **top 5 ranking candidates** from the comparative assessment shall be submitted to the Appointing Authority as the selection line-up.

**What's missing:** The deliberation module (`resources/js/Pages/Hrmpsb/Deliberation.vue`) allows entering rank and action for unlimited candidates. There is no validation that caps the recommendation at 5, no visual alert if more than 5 are marked "recommended/endorsed," and no auto-rejection of candidates ranked below 5th.

---

### 11. No Probationary Period Tracker

**Policy basis:** Under Section 11 of Book V, EO 292, permanent appointments serve a 6-month probationary period. At the end of this period, the appointing authority must either confirm or terminate the appointment. If not acted upon, the appointment is automatically confirmed.

**What's missing:** Once an application reaches `appointed` status, there is no automatic tracking of the 6-month probationary end date, no reminder notification to HR, and no confirmation/termination workflow.

---

## Part 3 — Functional Gaps in Existing Features

These are features that are partially implemented or structurally incomplete.

---

### 12. Exam/Interview Schedule Not Visible to Applicants

`ExaminationController` and `InterviewController` exist and work. `ExaminationScheduled` and `InterviewScheduled` notifications exist. But the **applicant dashboard has no UI to view their assigned schedule** — no date, time, or venue is ever surfaced to the applicant in the portal. They only receive an email. The dashboard's "My Applications" tab shows only a status badge.

**Fix:** Add an "Upcoming Schedule" section to the applicant dashboard that queries `exam_schedules` and `interview_schedules` for the current user's applications.

---

### 13. Document Verification Has No HR UI

`/api/documents/{document}/verify` route exists in `DocumentController`. But no admin/HR page has a "Verify Documents" button or document review interface. HR officers cannot mark documents as verified — the route is dead from the frontend.

**Fix:** Add a document verification panel in the application detail drawer in `resources/js/Pages/Admin/Applications.vue`, listing each uploaded document with a "Verified / Reject" toggle and a field for verification notes.

---

### 14. Reports Export JSON Only — Should Export Excel/CSV

`maatwebsite/excel` is installed. All 5 reports (`qualified-list`, `comparative-assessment`, `appointment-report`, `pipeline-summary`, `compliance-deadlines`) return structured JSON and currently only offer "Export JSON." Government offices process data in Excel/spreadsheets. The JSON export is not useful for a non-technical HR officer.

---

### 15. `isComplete()` Profile Check Is Too Shallow

**Location:** `app/Models/ApplicantProfile.php:54`

The completion check only validates 6 fields: `gender`, `civil_status`, `birthday`, `mobile_number`, `region`, `eligibility`. It does not check for:

- Required documents (`pds_path`, `app_letter_path`, `coe_path`, `tor_path`)
- At least one educational attainment record
- At least one work experience record (required for most SG levels)

An applicant can pass `isComplete()` and submit an application with no uploaded documents and no work history, which would then fail QS evaluation immediately. The gate should be stricter.

---

### 16. Anticipated Vacancy Has No Special Workflow

The `Vacancy` model has `is_anticipated_vacancy` (boolean, fillable, castable). However, per CSC policy, anticipated vacancies (positions that will soon become vacant due to retirement, resignation, etc.) have a specific publication and processing timeline. The field exists in the database but has zero special handling anywhere in the controllers, frontend, or reports.

---

### 17. Audit Log Scope Is Too Narrow

`AuditLog::record()` is only called from `VacancyController`. None of the following are logged:

- Application status changes (the biggest risk area for COA and CSC inspection)
- User account creation/deletion/role changes
- Document uploads/verifications
- QS evaluation submissions and locks
- BEI rating submissions and locks
- Deliberation decisions and unmask actions
- CS Form generation, signing, and submission

The audit log table exists but is essentially empty for all HRMPSB evaluation actions.

---

## Part 4 — Recommended Additional Features (Efficiency & Productivity)

These are new capabilities that would significantly improve process owner efficiency.

---

### 18. Automated Composite Score Ranking

After QS lock + exam results + BEI ratings are all complete, the system should auto-compute a weighted composite score per applicant and auto-rank them. The HRMPSB Secretariat currently has to manually compile these from three separate pages.

**Suggested formula** (configurable per CSC guidelines):

- QS Pass/Fail (eliminatory)
- Written Exam (TWE): configurable weight (e.g., 40%)
- CBWE: configurable weight (e.g., 30%)
- BEI Average: configurable weight (e.g., 30%)

This composite score should appear in the Deliberation page and pre-fill the rank column.

---

### 19. Bulk Schedule Generation

Currently, an exam or interview schedule must be created per-application. When a vacancy has 30 applicants to schedule for a written exam on the same day, HR must create 30 individual records.

**Suggested feature:** A "Batch Schedule" modal on the Applications page: pick a date, time, venue, and select multiple applicants — creates `exam_schedule` or `interview_schedule` records in bulk and fires `ExaminationScheduled` / `InterviewScheduled` notifications for all selected applicants.

---

### 20. Application Withdrawal Flow for Applicants

The `withdrawn` status exists but applicants have no way to withdraw. A "Withdraw Application" button (with a confirmation modal and a required reason field) should be added to the applicant's application list. Withdrawal should be blocked after the exam has been scheduled (as it disrupts the process).

---

### 21. In-App Notification Center

The database notification channel is already configured and notifications are written to the `notifications` table on every status change. But there is no bell icon, unread count, or notification list in either `ApplicantLayout` or `AdminLayout`. Applicants have no way to see these notifications without checking email.

---

### 22. Vacancy Duplication (Clone Feature)

When the same position becomes vacant repeatedly (e.g., "Administrative Aide VI"), HR must re-enter all qualification standards and competencies from scratch each time. A "Duplicate Vacancy" button that clones all fields (except dates and status) would eliminate significant repetitive data entry.

---

### 23. HRMPSB Meeting Minutes Auto-Generation

After deliberation is complete, the Secretariat must prepare official HRMPSB meeting minutes. The system has all the data (attendees via `hrmpsb_board_compositions`, applications evaluated, QS results, exam scores, BEI scores, final decisions, unmask timestamps). A "Generate Meeting Minutes" PDF button would save 1-2 hours of manual document preparation per vacancy.

---

### 24. HR Dashboard KPIs and Time-to-Fill Metrics

The admin dashboard shows basic counts. It does not track:

- **Time-to-fill**: Days from publication to appointment per vacancy
- **Time-per-stage**: How many days applications spent in each status
- **Bottleneck detection**: Which stage has the longest average dwell time
- **Compliance rate**: % of vacancies with no overdue compliance deadlines

These are PRIME-HRM Level 3 evidence requirements.

---

### 25. Bulk Excel Upload for Exam Results

Currently, exam results are entered one-by-one via the Exam Results page. In practice, written examinations produce score sheets with 20-50 applicants. A CSV/Excel upload template for exam results (applicant token + raw score + max score) would replace 30 individual form submissions with a single file upload.

---

### 26. Forgot Password / Password Reset

No reset flow exists. An applicant who forgets their password has no recovery path. This is a basic usability blocker for production go-live.

---

### 27. Email Verification for New Accounts

`User` model has `email_verified_at` column and the `MustVerifyEmail` interface is commented out. Unverified email addresses mean the system cannot reliably send exam/interview schedule notifications. If the email is wrong, the applicant loses all communication silently.

---

### 28. Pinia Auth Store

At minimum 9 Vue components independently call `JSON.parse(localStorage.getItem('auth_user'))` and `localStorage.getItem('auth_token')`. Pinia is already installed. A single `useAuthStore` would centralize auth state, make logout reliable across all open tabs, and eliminate the `authHeaders()` function copy-pasted into every component.

---

## Priority Matrix

| Priority | # | Item | Est. Effort | CSC Policy Risk |
|----------|---|------|-------------|----------------|
| **P0 — Now** | 1 | Fix 10-day → 15-day publication period | Minutes | Legal / appointment invalidation |
| **P0 — Now** | 3 | Create Authorization Policies | Half-day | Security breach |
| **P0 — Now** | 4 | Fix notification vacancy null crash | 1 hour | Queue worker goes down |
| **P1 — Before Go-Live** | 26 | Forgot password flow | 1 day | User blocked out |
| **P1 — Before Go-Live** | 27 | Email verification | Half-day | Silent comms failure |
| **P1 — Before Go-Live** | 12 | Applicant exam/interview schedule view | 1 day | Applicants miss exams |
| **P1 — Before Go-Live** | 15 | Widen `isComplete()` check | 2 hours | Bad applications submitted |
| **P1 — Before Go-Live** | 17 | Strengthen Audit Logs | Half-day | COA/CSC inspection failure |
| **P2 — Next Sprint** | 5 | Selection Line-Up publication | 3 days | CSC MC No. 22 compliance |
| **P2 — Next Sprint** | 6 | Protest period tracking | 2 days | CSC MC No. 22 compliance |
| **P2 — Next Sprint** | 10 | Top 5 rule enforcement in deliberation | 1 day | ORAOHRA compliance |
| **P2 — Next Sprint** | 14 | Excel/CSV report export | 1 day | Usability |
| **P2 — Next Sprint** | 13 | Document verification HR UI | 1 day | Process gap |
| **P3 — Roadmap** | 19 | Bulk scheduling | 2 days | Efficiency |
| **P3 — Roadmap** | 18 | Auto composite score ranking | 2 days | Efficiency |
| **P3 — Roadmap** | 20 | Applicant withdrawal flow | 1 day | UX |
| **P3 — Roadmap** | 21 | In-app notification center | 2 days | UX |
| **P3 — Roadmap** | 22 | Vacancy duplication / clone | 1 day | Efficiency |
| **P3 — Roadmap** | 25 | Bulk Excel upload for exam results | 2 days | Efficiency |
| **P3 — Roadmap** | 28 | Pinia auth store | 1 day | Code quality |
| **P4 — Future** | 11 | Probationary period tracker | 2 days | CSC compliance |
| **P4 — Future** | 23 | HRMPSB meeting minutes PDF | 3 days | Efficiency |
| **P4 — Future** | 24 | HR KPI dashboard / time-to-fill | 4 days | PRIME-HRM |
| **P4 — Future** | 8 | Nepotism / conflict of interest guard | 3 days | Legal |
| **P4 — Future** | 7 | Next-in-rank (QNR) checking | 5 days | CSC MC No. 22 |
| **P4 — Future** | 9 | Internal employee notification | 2 days | CSC policy |
| **P4 — Future** | 16 | Anticipated vacancy workflow | 2 days | CSC policy |
| **P4 — Future** | 2 | IPCR required for incumbent govt employees | 2 days | CSC MC No. 3 |

---

## Key Policy References

| Reference | Subject |
|-----------|---------|
| RA 7041 | Publication Law — 15-day minimum vacancy posting |
| EO 292, Book V | Administrative Code — civil service rules, nepotism, probationary period |
| CSC MC No. 22, s. 2011 | Revised Policies on Merit and Fitness in the Civil Service |
| CSC MC No. 3, s. 2001 | IPCR requirement for government employees |
| CSC MC No. 21, s. 2004 | Nepotism rules |
| 2017 ORAOHRA | Omnibus Rules on Appointments and Other Human Resource Actions — Top 5 rule |
| PRIME-HRM Framework | Level 3 evidence requirements including time-to-fill KPIs |
| RA 6713 | Code of Conduct and Ethical Standards for Public Officials |
