<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
  .page { padding: 20mm 15mm; }
  .header { text-align: center; margin-bottom: 10px; }
  .header h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; }
  .header h2 { font-size: 11pt; }
  .header p  { font-size: 9pt; color: #555; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  td, th { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
  th { background: #e8e8e8; font-weight: bold; text-align: left; }
  .label { font-size: 8pt; color: #555; display: block; }
  .value { font-size: 10pt; font-weight: bold; }
  .section-title { background: #2a338f; color: #fff; font-weight: bold; font-size: 9pt; padding: 4px 6px; }
  .sig-box { height: 50px; border-top: 1px solid #000; margin-top: 40px; text-align: center; font-size: 8pt; }
  .footer { margin-top: 20px; font-size: 8pt; color: #555; text-align: center; }
  .pnpki-placeholder { border: 2px dashed #999; padding: 8px; text-align: center; font-size: 8pt; color: #555; margin-top: 10px; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <h1>Republic of the Philippines</h1>
    <h2>Civil Service Commission</h2>
    <h1>CS Form No. 33-A</h1>
    <p>Revised 2017 | Appointment (Original / Promotion / Transfer / Reemployment / Reinstatement)</p>
  </div>

  <table>
    <tr>
      <th colspan="4" class="section-title">I. POSITION INFORMATION</th>
    </tr>
    <tr>
      <td width="50%">
        <span class="label">Position Title</span>
        <span class="value">{{ $vacancy->position_title ?? '—' }}</span>
      </td>
      <td width="25%">
        <span class="label">Item / Plantilla No.</span>
        <span class="value">{{ $vacancy->plantilla_no ?? '—' }}</span>
      </td>
      <td width="25%">
        <span class="label">Salary Grade</span>
        <span class="value">SG-{{ $vacancy->salary_grade ?? '—' }}</span>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="label">Place of Assignment</span>
        <span class="value">{{ $vacancy->place_of_assignment ?? 'CSC RO VIII' }}</span>
      </td>
      <td>
        <span class="label">Nature of Appointment</span>
        <span class="value">Permanent</span>
      </td>
    </tr>
  </table>

  <table>
    <tr>
      <th colspan="4" class="section-title">II. APPOINTEE INFORMATION</th>
    </tr>
    <tr>
      <td width="35%">
        <span class="label">Last Name</span>
        <span class="value">{{ strtoupper($profile->last_name ?? '—') }}</span>
      </td>
      <td width="35%">
        <span class="label">First Name</span>
        <span class="value">{{ $profile->first_name ?? '—' }}</span>
      </td>
      <td width="20%">
        <span class="label">Middle Name</span>
        <span class="value">{{ $profile->middle_name ?? '—' }}</span>
      </td>
      <td width="10%">
        <span class="label">Suffix</span>
        <span class="value">{{ $profile->suffix ?? '' }}</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="label">Date of Birth</span>
        <span class="value">{{ $profile->birthday ? \Carbon\Carbon::parse($profile->birthday)->format('F d, Y') : '—' }}</span>
      </td>
      <td>
        <span class="label">Place of Birth</span>
        <span class="value">—</span>
      </td>
      <td>
        <span class="label">Civil Status</span>
        <span class="value">{{ ucfirst($profile->civil_status ?? '—') }}</span>
      </td>
      <td>
        <span class="label">Sex</span>
        <span class="value">{{ ucfirst($profile->gender ?? '—') }}</span>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="label">CS Eligibility / License</span>
        <span class="value">{{ $profile->eligibility ?? '—' }}</span>
      </td>
      <td colspan="2">
        <span class="label">Mobile No.</span>
        <span class="value">{{ $profile->mobile_number ?? '—' }}</span>
      </td>
    </tr>
  </table>

  <table>
    <tr>
      <th colspan="3" class="section-title">III. EDUCATIONAL BACKGROUND</th>
    </tr>
    @forelse($profile->educationalAttainments ?? [] as $edu)
    <tr>
      <td>{{ $edu->school ?? '—' }}</td>
      <td>{{ $edu->degree ?? '—' }}</td>
      <td>{{ $edu->year_graduated ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="3">—</td></tr>
    @endforelse
  </table>

  <table>
    <tr>
      <th colspan="4" class="section-title">IV. WORK EXPERIENCE</th>
    </tr>
    @forelse($profile->workExperiences ?? [] as $exp)
    <tr>
      <td>{{ $exp->position_title ?? '—' }}</td>
      <td>{{ $exp->company ?? $exp->employer ?? '—' }}</td>
      <td>{{ $exp->date_from ?? '—' }}</td>
      <td>{{ $exp->date_to ?? 'Present' }}</td>
    </tr>
    @empty
    <tr><td colspan="4">—</td></tr>
    @endforelse
  </table>

  <!-- Certification -->
  <table style="margin-top: 15px;">
    <tr>
      <td width="60%">
        <p style="font-size:8pt; margin-bottom:8px;">
          I hereby certify that the above information is true and correct to the best of my knowledge.
        </p>
        <div class="sig-box">Signature of Appointee over Printed Name / Date</div>
      </td>
      <td width="40%">
        <p style="font-size:8pt; margin-bottom:8px;">
          Approved by:
        </p>
        <div class="sig-box">Appointing Authority / Date</div>
      </td>
    </tr>
  </table>

  <div class="pnpki-placeholder">
    [ PNPKI Digital Signature Placeholder — To be signed electronically via PNPKI once credentials are provisioned ]
  </div>

  <div class="footer">
    Generated by CSC RO VIII - Recruitment Portal &bull; {{ $generatedAt->format('F d, Y h:i A') }}
  </div>
</div>
</body>
</html>
