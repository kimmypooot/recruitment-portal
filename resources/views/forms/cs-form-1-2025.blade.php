<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 9pt; color: #000; }
  .page { padding: 15mm 12mm; }
  .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #2a338f; padding-bottom: 8px; }
  .header h1 { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
  .header h2 { font-size: 10pt; margin-top: 2px; }
  .header p  { font-size: 8pt; color: #555; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  td, th { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
  th { background: #e8e8e8; font-weight: bold; text-align: left; font-size: 8pt; }
  .label { font-size: 7pt; color: #666; display: block; }
  .value { font-size: 9pt; }
  .section-title { background: #2a338f; color: #fff; font-weight: bold; font-size: 8pt; padding: 3px 5px; }
  .sig-box { height: 45px; border-top: 1px solid #000; margin-top: 35px; text-align: center; font-size: 7pt; padding-top: 4px; }
  .pnpki-placeholder { border: 2px dashed #999; padding: 6px; text-align: center; font-size: 7pt; color: #555; margin-top: 8px; }
  .footer { margin-top: 15px; font-size: 7pt; color: #555; text-align: center; }
  .two-col td { width: 50%; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <h1>Personal Data Sheet</h1>
    <h2>Civil Service Commission — CS Form No. 1 (Revised 2025)</h2>
    <p>WARNING: Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case(s) against the person concerned.</p>
  </div>

  <!-- I. Personal Information -->
  <table>
    <tr><th colspan="6" class="section-title">I. PERSONAL INFORMATION</th></tr>
    <tr>
      <td colspan="2"><span class="label">SURNAME</span><span class="value">{{ strtoupper($profile->last_name ?? '—') }}</span></td>
      <td colspan="2"><span class="label">FIRST NAME</span><span class="value">{{ $profile->first_name ?? '—' }}</span></td>
      <td><span class="label">MIDDLE NAME</span><span class="value">{{ $profile->middle_name ?? '—' }}</span></td>
      <td><span class="label">SUFFIX</span><span class="value">{{ $profile->suffix ?? '' }}</span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="label">DATE OF BIRTH (mm/dd/yyyy)</span><span class="value">{{ $profile->birthday ? \Carbon\Carbon::parse($profile->birthday)->format('m/d/Y') : '—' }}</span></td>
      <td colspan="2"><span class="label">PLACE OF BIRTH</span><span class="value">—</span></td>
      <td><span class="label">SEX</span><span class="value">{{ ucfirst($profile->gender ?? '—') }}</span></td>
      <td><span class="label">CIVIL STATUS</span><span class="value">{{ ucfirst($profile->civil_status ?? '—') }}</span></td>
    </tr>
    <tr>
      <td colspan="3"><span class="label">MOBILE NUMBER</span><span class="value">{{ $profile->mobile_number ?? '—' }}</span></td>
      <td colspan="3"><span class="label">REGION OF RESIDENCE</span><span class="value">{{ $profile->region ?? '—' }}</span></td>
    </tr>
  </table>

  <!-- II. CS Eligibility -->
  <table>
    <tr><th colspan="4" class="section-title">II. CIVIL SERVICE ELIGIBILITY</th></tr>
    <tr>
      <th>Career Service / RA 1080 / OTHERS</th>
      <th>Rating (if applicable)</th>
      <th>Date of Examination / Conferment</th>
      <th>Place of Examination / Conferment</th>
    </tr>
    <tr>
      <td>{{ $profile->eligibility ?? '—' }}</td>
      <td>—</td>
      <td>—</td>
      <td>—</td>
    </tr>
  </table>

  <!-- III. Work Experience -->
  <table>
    <tr><th colspan="6" class="section-title">III. WORK EXPERIENCE (Include private employment)</th></tr>
    <tr>
      <th>Inclusive Dates (From)</th>
      <th>Inclusive Dates (To)</th>
      <th>Position Title</th>
      <th>Department / Agency / Office / Company</th>
      <th>Monthly Salary</th>
      <th>Status of Appointment</th>
    </tr>
    @forelse($profile->workExperiences ?? [] as $exp)
    <tr>
      <td>{{ $exp->date_from ?? '—' }}</td>
      <td>{{ $exp->date_to ?? 'Present' }}</td>
      <td>{{ $exp->position_title ?? '—' }}</td>
      <td>{{ $exp->company ?? $exp->employer ?? '—' }}</td>
      <td>{{ isset($exp->monthly_salary) ? number_format($exp->monthly_salary, 2) : '—' }}</td>
      <td>{{ $exp->status ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center;">N/A</td></tr>
    @endforelse
  </table>

  <!-- IV. Education -->
  <table>
    <tr><th colspan="5" class="section-title">IV. EDUCATIONAL BACKGROUND</th></tr>
    <tr>
      <th>Level</th>
      <th>School Name</th>
      <th>Degree / Course</th>
      <th>Period of Attendance (From–To)</th>
      <th>Year Graduated / Highest Level</th>
    </tr>
    @forelse($profile->educationalAttainments ?? [] as $edu)
    <tr>
      <td>{{ $edu->level ?? '—' }}</td>
      <td>{{ $edu->school ?? '—' }}</td>
      <td>{{ $edu->degree ?? '—' }}</td>
      <td>{{ ($edu->year_from ?? '—') . ' – ' . ($edu->year_to ?? '—') }}</td>
      <td>{{ $edu->year_graduated ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="5" style="text-align:center;">N/A</td></tr>
    @endforelse
  </table>

  <!-- V. Trainings -->
  <table>
    <tr><th colspan="4" class="section-title">V. LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS / TRAINING PROGRAMS ATTENDED</th></tr>
    <tr>
      <th>Title of L&D / Training</th>
      <th>Date (From–To)</th>
      <th>No. of Hours</th>
      <th>Conducted / Sponsored by</th>
    </tr>
    @forelse($profile->trainings ?? [] as $t)
    <tr>
      <td>{{ $t->title ?? '—' }}</td>
      <td>{{ ($t->date_from ?? '—') . ' – ' . ($t->date_to ?? '—') }}</td>
      <td>{{ $t->hours ?? '—' }}</td>
      <td>{{ $t->conducted_by ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="4" style="text-align:center;">N/A</td></tr>
    @endforelse
  </table>

  <!-- Declaration -->
  <table style="margin-top: 10px;">
    <tr>
      <td>
        <p style="font-size:8pt;">
          I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct and complete statement pursuant to the provisions of pertinent laws, rules and regulations of the Republic of the Philippines. I authorize the agency to verify/validate the contents stated herein.
        </p>
        <div class="sig-box">Signature / Date</div>
        <p style="font-size:7pt; margin-top:4px;">Subscribed and sworn to before me this ___ day of __________, 20___, at _____________________.</p>
      </td>
    </tr>
  </table>

  <div class="pnpki-placeholder">
    [ PNPKI Digital Signature Placeholder — To be signed electronically via PNPKI ]
  </div>

  <div class="footer">
    CS Form No. 1 (Revised 2025) &bull; Generated by CSC RO VIII Recruitment System &bull; {{ $generatedAt->format('F d, Y h:i A') }}
  </div>
</div>
</body>
</html>
