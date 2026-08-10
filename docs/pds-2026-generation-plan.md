# Plan — Generate CS Form No. 212 (Revised 2026) Personal Data Sheet from the Applicant Profile

Status: **plan only, nothing implemented.**
Author: drafted 2026-08-10.

---

## 1. Governing issuance

- **CSC Memorandum Circular No. 05, s. 2026** (issued 23 June 2026) — *Adoption of CS Form No. 212, Revised 2026 (Personal Data Sheet)*.
- The Revised **2025** release shipped with a formatting defect: Section V (item 28, Work Experience) accidentally **dropped the `MONTHLY SALARY` and `SALARY/JOB/PAY GRADE & STEP INCREMENT` columns**. The Revised **2026** form restores them. Everything else is unchanged between 2025 and 2026.
- A **3-month transitory period** ran from the MC's issuance, so 2025 forms lapse around late September 2026. **Build against 2026 only** — do not implement a 2025 mode.

Field structure below was transcribed from the official Revised 2025 PDF (`ots.gov.ph`), then reconciled with the 2026 item-28 delta. It is authoritative for section/field/column names.

### Notable changes vs. Revised 2017

| Change | Detail |
|---|---|
| New ID fields | **PhilSys Number (PSN)** (item 13), **UMID ID No.** |
| Renamed | item 5 is now **`SEX AT BIRTH`** (was `SEX`) |
| Eligibility header | now `CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/BAR)/UNDER SPECIAL LAWS/CATEGORY II/IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL`; date/place columns are `…OF EXAMINATION/CONFERMENT` |
| References (Q41) | third column is now `CONTACT NO. AND/OR EMAIL` (was tel. no. only) |
| Q40 statutory basis | cites RA 8371, RA 7277 as amended, and **RA 11861 (Expanded Solo Parents Welfare Act)** |
| Signature blocks | every page footer reads **`wet signature / e-signature / digital certificate`** — digital signing is now explicitly sanctioned |
| Attachment | duties are no longer inline; they go on the separate **Work Experience Sheet** |

---

## 2. Authoritative field inventory vs. current schema

Legend: ✅ modelled · ⚠️ partial · ❌ absent

### Section I — Personal Information (items 1–21)

| Item | Fields | Status |
|---|---|---|
| 1–3 | Surname, First Name, Middle Name, **Name Extension (JR., SR)** | ✅ `users.first_name/last_name/middle_name/suffix` |
| 4 | Place of Birth | ❌ |
| 5 | Sex at Birth (Male/Female) | ⚠️ `applicant_profiles.gender` — verify allowed values |
| 6 | Civil Status (Single/Married/Widowed/Separated/**Other/s: ___**) | ⚠️ have `civil_status`, no free-text "other" |
| 7 | Date of Birth (dd/mm/yyyy) | ✅ `birthday` |
| 9 | Height (m), Weight (kg), Blood Type | ❌ |
| 10–15 | GSIS, Pag-IBIG, PhilHealth, SSS, TIN, **UMID ID No.**, **PhilSys No. (PSN)**, Agency Employee No. | ❌ all |
| 16 | Citizenship — Filipino / Dual; by birth / by naturalization; country | ❌ |
| 17 | **Residential Address** — House/Block/Lot, Street, Subdivision/Village, Barangay, City/Municipality, Province, **Zip Code** | ⚠️ have flat `barangay/city_municipality/province/region`; missing house/street/subdivision/zip |
| 18 | **Permanent Address** — same 7 sub-fields | ❌ entirely |
| 19–21 | Telephone No., Mobile No., E-mail | ⚠️ mobile ✅, email ✅ (`users`), telephone ❌ |

### Section II — Family Background (22–25) — ❌ nothing exists

- 22 Spouse: Surname, First, Middle, Name Extension, Occupation, Employer/Business Name, Business Address, Telephone No.
- 23 **Name of Children** (repeating): Full Name + Date of Birth
- 24 Father: Surname, First, Middle, Name Extension
- 25 **Mother's Maiden Name**: Surname, First, Middle

### Section III — Educational Background (26) — ✅ complete

Levels: Elementary, Secondary, **Vocational/Trade Course**, College, Graduate Studies.
Columns: Name of School · Basic Education/Degree/Course · Period of Attendance (From/To) · Highest Level/Units Earned · Year Graduated · Scholarship/Academic Honors Received.
→ `educational_attainments` matches field-for-field. Only fix: normalise the `level` vocabulary to include `vocational_trade`.

### Section IV — Civil Service Eligibility (27) — ❌ needs its own table

Columns: Eligibility · Rating (if applicable) · Date of Examination/Conferment · Place of Examination/Conferment · License: Number, Valid Until.
→ Today this is a **single string** `applicant_profiles.eligibility`. The PDS needs a repeating table.

### Section V — Work Experience (28) — ✅ complete (and 2026-aligned)

Columns: Inclusive Dates (From/To) · Position Title · Department/Agency/Office/Company · **Monthly Salary** · **Salary/Job/Pay Grade & Step Increment** · Status of Appointment · Gov't Service (Y/N).
→ `work_experiences` already carries `monthly_salary` and `salary_grade`, so it lines up with 2026 (not the defective 2025). Only fix: `date_from`/`date_to` are `string` and should be `date`.

### Section VI — Voluntary Work (29) — ❌ needs a table

Name & Address of Organization · Inclusive Dates (From/To) · Number of Hours · Position/Nature of Work.

### Section VII — Learning & Development (30) — ✅ complete

Title · Inclusive Dates of Attendance (From/To) · Number of Hours · Type of L&D (Managerial/Supervisory/Technical/etc.) · Conducted/Sponsored By.
→ `trainings` matches field-for-field.

### Section VIII — Other Information (31–33) — ❌ needs a table

31 Special Skills and Hobbies · 32 Non-Academic Distinctions/Recognition · 33 Membership in Association/Organization. Each is a repeating list (~7 rows on the printed form).

### Page 4 — Questions 34–42 — ❌ almost entirely absent

| Q | Content |
|---|---|
| 34 | Related by consanguinity/affinity to appointing/recommending authority — a) within 3rd degree, b) within 4th degree (LGU career employees) + details |
| 35 | a) found guilty of administrative offence, b) criminally charged before any court (+ **Date Filed**, **Status of Case/s**) |
| 36 | Convicted of any crime/violation |
| 37 | Separated from service (resignation, retirement, dropped from rolls, dismissal, termination, end of term, finished contract, phased out) |
| 38 | a) candidate in a national/local election in the last year, b) resigned from government within 3 months before the last election to campaign |
| 39 | Immigrant / permanent resident of another country (+ country) |
| 40 | a) member of an indigenous group, b) person with disability (+ **ID No.**), c) solo parent (+ **ID No.**) |
| 41 | **References** — 3 rows: Name · Office/Residential Address · Contact No. and/or Email |
| 42 | Government Issued ID · ID/License/Passport No. · Date/Place of Issuance; plus Photo (4.5cm × 3.5cm, unfiltered, taken within last 6 months), Right Thumbmark, Signature, Date Accomplished, Person Administering Oath |

→ Only `indigenous_group`, `pwd`, `solo_parent` exist (partial Q40). `photo_path` covers the Q42 photo.

**Headline: 3 of 8 sections are already modelled. Sections I (partial), II, IV, VI, VIII and all of page 4 are new work.**

---

## 3. Proposed schema

### 3.1 Extend `applicant_profiles` (Section I only)

```
place_of_birth, height_m DECIMAL(4,2), weight_kg DECIMAL(5,2), blood_type,
civil_status_other,
citizenship ENUM('filipino','dual'), citizenship_basis ENUM('by_birth','by_naturalization'), dual_citizenship_country,
gsis_id_no, pagibig_id_no, philhealth_no, sss_no, tin_no, umid_id_no, philsys_number, agency_employee_no,
telephone_no,
res_house_block_lot, res_street, res_subdivision, res_barangay, res_city_municipality, res_province, res_zip_code,
perm_house_block_lot, perm_street, perm_subdivision, perm_barangay, perm_city_municipality, perm_province, perm_zip_code,
pds_generated_at
```

Keep the existing `region` column — the PDS has no region field, but it is used elsewhere for reporting. Backfill `res_*` from the current `barangay/city_municipality/province` in the same migration.

### 3.2 New tables

| Table | Cardinality | Columns |
|---|---|---|
| `pds_family_backgrounds` | hasOne | `spouse_{surname,first_name,middle_name,name_extension,occupation,employer,business_address,telephone}`, `father_{surname,first_name,middle_name,name_extension}`, `mother_maiden_{surname,first_name,middle_name}` |
| `pds_children` | hasMany | `full_name`, `date_of_birth` |
| `civil_service_eligibilities` | hasMany | `eligibility_name`, `rating`, `examination_date`, `examination_place`, `license_number`, `license_valid_until` |
| `voluntary_works` | hasMany | `organization_name_address`, `date_from`, `date_to`, `number_of_hours`, `position_nature_of_work` |
| `pds_other_infos` | hasMany | `kind ENUM('special_skill_hobby','non_academic_distinction','membership')`, `value` |
| `pds_references` | hasMany (3) | `name`, `office_residential_address`, `contact_no_or_email` |
| `pds_declarations` | hasOne | Q34–Q40 boolean + detail pairs, `pwd_id_no`, `solo_parent_id_no`, `government_id_type`, `government_id_number`, `government_id_issuance_date`, `government_id_issuance_place`, `date_accomplished`, `signature_path`, `right_thumbmark_path` |

All keyed on `applicant_profile_id` with `onDelete('cascade')`, matching the existing `work_experiences` / `trainings` convention.

Putting Q34–42 in `pds_declarations` rather than bolting ~25 more columns onto `applicant_profiles` keeps the profile table workable and gives the page-4 attestation its own lifecycle (it is re-attested per submission, not per profile edit).

### 3.3 Alter

- `work_experiences.date_from` / `.date_to`: `string` → `date`.
- `educational_attainments.level`: normalise vocabulary, add `vocational_trade`.

---

## 4. Rendering — the important engineering decision

### 4.0 Anatomy of the official workbook (verified by inspection, 2026-08-10)

Source file: `CS-Form-No.-212-Revised-2026-PDS.xlsx` (103,801 bytes). Confirmed genuine 2026 — the string
`SALARY/ JOB/ PAY GRADE (If applicable) & STEP INCREMENT` and `MONTHLY SALARY` are both present in Section V,
i.e. the columns MC 05 s. 2026 restored. `Revised 2026` appears in all four page footers.

| Aspect | Finding |
|---|---|
| Sheets | `C1`, `C2`, `C3`, `C4` — one per printed page — plus a hidden `Lookup` sheet |
| Print areas | `C1!A1:N61`, `C2!A2:M48`, `C3!A1:K51`, `C4!A1:M71` |
| Merged cells | 140 / 127 / 83 / 85 (435 total) — the layout is almost entirely merge-driven |
| Form controls | **35 checkboxes**, 1 dropdown, 1 label — 12 on `C1` (sex at birth, civil status, citizenship), 25 on `C4` (the Q34–Q40 YES/NO pairs) |
| Checkbox → cell link | **None.** Zero controls have `fmlaLink`. State lives only in the VML shape, not in any cell |
| Control storage | `xl/ctrlProps/ctrlProp*.xml` + VML shapes in `xl/drawings/vmlDrawing*.vml`, anchored by `<x:Anchor>col,dx,row,dy,col2,dx2,row2,dy2` |
| Data validation | None on the four form sheets |

### 4.1 Excel (.xlsx) — populate the official template, do not rebuild it

`maatwebsite/excel` (PhpSpreadsheet) is **already in `composer.json` and currently unused**, so this costs no new dependency.

Do **not** construct the spreadsheet from scratch:

1. Commit the official CSC file to `resources/templates/CS_Form_212_Revised_2026.xlsx`.
   *(Deviation from the original plan: `storage/app/` is covered by a blanket `*` in `storage/app/.gitignore`,
   so the template would not have been committed. It is a static source asset, not runtime storage, so
   `resources/` is the correct home and is tracked by default.)*
2. `PhpSpreadsheet\IOFactory::load()` it and write values into the known cell addresses.
3. Post-process the checkboxes (§4.2).
4. Save to a temp path and stream.

Every merge, border, column width, print area and page break comes from CSC's own file, so the layout is unarguably
compliant. This eliminates the largest risk in the feature — CSC rejecting the form for layout deviation.

**A load → save round-trip through PhpSpreadsheet was tested against the real file. Results:**

- ✅ all 435 merged cells preserved exactly, per sheet
- ✅ all 4 print areas preserved exactly
- ✅ all 37 `ctrlProps` preserved
- ✅ VML drawings preserved with **byte-identical anchors**
- ✅ `printerSettings` preserved
- ⚠️ `customXml/item*.xml` + `itemProps*.xml` dropped (Office document-metadata parts — irrelevant to the form)
- ⚠️ drawing parts renumbered (`drawing2.xml` → `drawing4.xml`), so **resolve VML by sheet rels, never by filename**
- File shrinks 103,801 → 82,811 bytes (recompression + dropped customXml)

So the template approach is sound. The one-off cost is the **cell-address map** (PDS field → `'C1'!C17`), built by hand
against the real file. Budget a focused half-day; store it in `config/pds_cell_map.php` so it is auditable and fixable
without touching code.

### 4.2 The checkboxes need a post-pass

This is the one thing PhpSpreadsheet cannot do. It *preserves* the 35 checkboxes but exposes no API to tick them, and
because none has an `fmlaLink`, there is no cell you can write to set one.

After PhpSpreadsheet saves, run a small post-processing step over the resulting `.xlsx` (it is a ZIP):

1. Open the archive, resolve each sheet's `legacyDrawing` relationship to its `vmlDrawing*.vml`.
2. For each box that should be ticked, inject `<x:Checked>1</x:Checked>` into that shape's `<x:ClientData>`
   (the blank template contains no `x:Checked` tags at all — every box starts unchecked).
3. Rewrite that one part and re-zip.

Shapes are identified by their `<x:Anchor>` cell coordinates, which are stable across the round-trip, so the map is
`field → (sheet, anchor)`. Build it once into `config/pds_checkbox_map.php` alongside the cell map.

Scope: 12 boxes on page 1, 23 on page 4 (Q34a/b, 35a/b, 36, 37, 38a/b, 39, 40a/b/c — YES and NO each).

Suggested home: `app/Services/Pds/XlsxCheckboxWriter.php`. Keep it separate from the cell-writing generator; it is
pure archive manipulation and should be independently testable.

### PDF — two options

**Option A (recommended if you control the server): render the populated .xlsx to PDF via headless LibreOffice.**
`soffice --headless --convert-to pdf`. One code path, guaranteed identical to the Excel output, near-zero layout work. Requires LibreOffice installed on the host — trivial locally under XAMPP, needs checking on the production box.

**Option B: hand-build a Blade template** at `resources/views/forms/cs-form-212-2026.blade.php` and render with DomPDF, matching the existing `cs-form-33a` pattern.
Consistent with the rest of the codebase and no new server dependency, but the PDS is four pages of extremely dense nested tables at ~7pt. DomPDF will fight you, and the result will only ever *approximate* the official layout. Realistically several days of pixel-chasing.

**Recommendation: Option A**, with Option B kept in reserve. Confirm LibreOffice availability on the deployment target before committing.

---

### 4.4 Why PhpSpreadsheet's PDF writers were rejected (measured, 2026-08-10)

The obvious way to avoid a LibreOffice dependency is PhpSpreadsheet's built-in PDF writers. It does not work
for this form. Rendering `pds-sample.xlsx` through the Dompdf writer produced:

- **7 pages instead of 4** — the PDS must be exactly four pages
- **all 35 checkbox captions gone** ("Filipino" and friends absent from the output) — the writer renders the
  sheet as HTML, and form controls have no HTML equivalent

A PDS with no tick boxes and wrong pagination is not a PDS. (Mpdf and Tcpdf backends are not installed;
they render through the same HTML path and would fail identically.)

Headless LibreOffice converts the real workbook, so the PDF and the .xlsx are the same document rendered once.
`PdsPdfGenerator` gives each conversion its own `-env:UserInstallation` profile so concurrent requests do not
serialise on LibreOffice's shared profile lock, and cleans up its temp directory on both success and failure.

## 4.3 Filling conventions (from the official Guide)

Transcribed from `Guide to Filling Up the Personal Data Sheet Revised 2026.pdf`. All of these are
implemented in `PdsDataAssembler`, not in the renderers, so the PDF and Excel outputs cannot drift apart.

| Rule | Where it applies |
|---|---|
| **"Do not leave blank entries. Put N/A if not applicable."** | every scalar field |
| **"Dates are in numeric format: dd/mm/yyyy."** | every date, including date of birth and all inclusive dates |
| "Indicate monthly salary in figures (e.g., P21,877.00)" | Section V |
| Salary/Job/Pay Grade & Step Increment formatted `00-0` (e.g. `24-2`) | Section V |
| "Indicate FULL name of schools. DO NOT ABBREVIATE." | Section III |
| "Indicate FULL position titles and COMPLETE NAME of department/agency/office/company." | Section V |
| "Indicate the FULL name of institution/agency that conducted or sponsored the program" (e.g. CSC → Civil Service Commission) | Section VII |
| Spouse and parents are written **surname, first name, name extension, middle name** | Section II |
| Children: "List full names (first name and surname) of ALL your children." | Section II item 23 |
| References use **FIRST NAME, MI, SURNAME** — a different order from Section II | Page 4 item 41 |

Two deliberate departures from a naive reading of the N/A rule:

- **Unused rows of a repeating table stay empty, not "N/A".** The rule is about unanswered *fields*; stamping
  N/A down 28 blank work-experience rows would be wrong. `PdsDataAssembler::PRINTED_ROWS` pads each list to the
  row count printed on the blank form and truncates overflow (overflow belongs on a continuation sheet).
- **Unanswered yes/no questions stay `null`, never `false`.** The checkbox writer must be able to leave *both*
  boxes blank. Rendering an unanswered question as "NO" would put a false sworn statement on the form.

## 5. Backend structure

```
app/Services/Pds/PdsDataAssembler.php   # profile + relations -> normalised array; "N/A" defaults, dd/mm/yyyy dates
app/Services/Pds/PdsExcelGenerator.php  # template load + cell map write
app/Services/Pds/XlsxCheckboxWriter.php # post-pass: inject <x:Checked>1</x:Checked> into VML (see 4.2)
app/Services/Pds/PdsPdfGenerator.php    # xlsx -> pdf (Option A) or Blade+DomPDF (Option B)
app/Services/Pds/PdsCompletenessChecker.php  # which PDS sections are still incomplete
app/Http/Controllers/PdsController.php
config/pds_cell_map.php
config/pds_checkbox_map.php
```

**Do not reuse `FormGeneratorService` / `CsForm`.** Both are `Application`-scoped — `generate()` requires an `Application` and writes a `CsForm` row keyed to it. The PDS belongs to the `ApplicantProfile` and exists independently of any application. Forcing it through `CsForm` would mean fabricating an application context. Keep it a separate service; add `CsForm::TYPES['212']` later only if HR needs a PDS snapshot frozen against a specific application.

Routes (`routes/api.php`, under `auth:sanctum`):

```
GET  /api/profile/pds/completeness   -> per-section completion state
GET  /api/profile/pds/download       -> ?format=pdf|xlsx
```

Authorization: owner-only. Add an `ApplicantProfilePolicy` (there isn't one yet) rather than an inline `$request->user()->id === …` check, matching the existing policy convention. HR/HRMPSB read access to a candidate's generated PDS is a **separate, later** decision — the current pipeline is built around anonymised deliberation (`AnonymizationService`), and an unrestricted PDS endpoint would leak identity into the blind stages. Flagging this explicitly: **do not expose a board-facing PDS route without deciding how it interacts with `AnonymizationToken`.**

Log generation via `AuditLog::record('pds_generated', $profile)`.

---

## 6. Frontend

`resources/js/Pages/Applicant/CompleteProfile.vue` is 1,194 lines with three tabs (`PersonalInfoTab`, `QualificationsTab`, `DocumentsTab`). The PDS needs roughly triple the input surface, so this file should not simply grow.

Proposed: keep the three top-level tabs but restructure the middle one into accordion sections mirroring the PDS itself, and split each into its own component under `Pages/Applicant/Profile/`:

```
PersonalInfoTab.vue   -> Section I (expanded: IDs, citizenship, both addresses, physical)
PdsTab.vue            -> accordions:
                           FamilyBackgroundSection.vue     (II)
                           EducationSection.vue            (III, existing logic)
                           EligibilitySection.vue          (IV, new repeating)
                           WorkExperienceSection.vue       (V, existing logic)
                           VoluntaryWorkSection.vue        (VI, new)
                           LearningDevelopmentSection.vue  (VII, existing logic)
                           OtherInformationSection.vue     (VIII, new)
                           DeclarationsSection.vue         (Q34-42)
DocumentsTab.vue      -> unchanged, plus the Generate PDS panel
```

The Generate PDS panel shows a per-section completeness checklist driven by `/api/profile/pds/completeness`, with **Download PDF** and **Download Excel** buttons disabled until the mandatory sections are green.

---

## 7. Interaction with the existing PDS upload — handle carefully

`ApplicantProfile::hasRequiredDocuments()` currently requires `pds_path` (an uploaded file), and applications cannot be submitted without it. Changing this naively will break every in-flight applicant.

Migration path:

1. **Phase 1** — generation is purely additive. The upload stays mandatory. Applicants can generate a PDS and upload it themselves.
2. **Phase 2** — `hasRequiredDocuments()` accepts *either* an uploaded `pds_path` *or* a complete generated PDS (`pds_generated_at` set and completeness check passing).
3. **Phase 3** — retire the upload for new applicants; keep it readable for historical records.

Do not skip to phase 3.

---

## 8. Suggested sequencing

| Phase | Scope |
|---|---|
| 1 | ✅ **Done** — migrations `2026_08_10_100001`–`100009`, 7 new models, relations on `ApplicantProfile`. No UI. Verified: rollback/re-migrate clean on MySQL, 58/58 tests pass on SQLite. |
| 2 | ✅ **Done** — `PdsDataAssembler`, `PdsCompletenessChecker`, `ApplicantProfileFactory::pdsPersonalInfo()`, 21 tests. Also fixed a phase-1 omission: `WorkExperience`/`Training` were missing `date` casts after their columns were converted. |
| 3 | ✅ **Done** — official template committed to `resources/templates/`, `config/pds_cell_map.php`, `config/pds_checkbox_map.php`, `PdsExcelGenerator`, `XlsxCheckboxWriter`, 12 tests. Still to wire: the controller/route that streams it (phase 5). |
| 4 | ⚠️ **Code complete, unverified** — `PdsPdfGenerator` + `config/pds.php`. Option A (LibreOffice) chosen; Option B and PhpSpreadsheet's own PDF writers were tested and rejected (see 4.4). **The render test is skipped until LibreOffice is installed on a host.** |
| 5a | ✅ **Backend done** — `PdsController`, `ApplicantProfilePolicy`, routes, 14 tests. Board access granted per decision of 2026-08-10. |
| 5b | ✅ **Done** — write API (`PdsProfileController`), `pdsApi`, `GeneratePdsPanel.vue`, and the **PDS Details** tab covering every section: Section I extras, Family Background + children, eligibility (IV), voluntary work (VI), other information (VIII), page-4 declarations, and references. Verified by `PdsEndToEndTest`, which fills the whole form over HTTP and generates it. |
| 6 | Flip `hasRequiredDocuments()` to phase-2 semantics. |
| 7 | *Later:* Work Experience Sheet attachment; e-signature/digital certificate via `PnpkiService` (the 2026 form explicitly permits it — this is where that stub stops being a stub). |

---

## 9. Open items

1. ~~Obtain the official Revised 2026 `.xlsx`.~~ **Done** — `CS-Form-No.-212-Revised-2026-PDS.xlsx` and
   `Guide to Filling Up the Personal Data Sheet Revised 2026.pdf` are in the user's Downloads, verified genuine (§4.0).
   Still to do: copy the `.xlsx` into `storage/app/templates/` and commit it. The separate **Work Experience Sheet**
   attachment has not been obtained yet (only needed for phase 7).
   *Note: several near-identical files sit alongside it in Downloads — `PDS LATEST.xlsx`, `Beebee_CS-Form-No.-212-Revised-2026-PDS.xlsx`,
   `…MODIFIED VERSION BY PCSMP…`, and 2025-vintage copies. Use only the pristine `CS-Form-No.-212-Revised-2026-PDS.xlsx`.*
2. **Blocking for phase 4 Option A:** confirm LibreOffice is installable on the production host.
3. Confirm whether HR/HRMPSB should ever see a generated PDS, and how that squares with blind deliberation (§5).
4. Q42 requires a **right thumbmark** image and a signature image. Decide whether to capture these in-app (canvas/upload) or leave the boxes blank for wet signing after print. Phase 1 recommendation: leave blank.
5. The photo spec is exact (4.5cm × 3.5cm, unfiltered, taken within the last 6 months). `photo_path` exists but is unconstrained — `cropperjs` is already installed-but-unused and would fit here.

---

## Sources

- [CSC MC 05, s. 2026 — Adoption of CS Form No. 212, Revised 2026](https://www.csguide.org/items/show/1575)
- [DM No. 481 s. 2026 — dissemination of CSC MC 05 s. 2026](https://www.depeddasma.edu.ph/dm-no-481-s-2026-dissemination-of-civil-service-commission-memorandum-circular-no-05-s-2026-re-adoption-of-cs-form-no-212-revised-2026-personal-data-sheet/)
- [CSC downloads — CSC Form 212 (Revised 2026)](https://csc.gov.ph/downloads/category/540-csc-form-212-revised-2025-personal-data-sheet)
- [CS Form No. 212 Revised 2025 PDS (PDF, used for field transcription)](https://ots.gov.ph/wp-content/uploads/2025/10/CS-Form-No-212-Revised-2025-PDS.pdf)
- [Personal Data Sheet (CS Form No. 212, Revised 2025) — COA](https://www.coa.gov.ph/wpfd_file/personal-data-sheet-cs-form-no-212-revised-2025-2/)
