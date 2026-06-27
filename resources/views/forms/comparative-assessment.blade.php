<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 8.5pt; color: #000; }
  .page { padding: 15mm 12mm; }
  .header { text-align: center; margin-bottom: 12px; }
  .header h1 { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
  .header h2 { font-size: 10pt; font-weight: bold; }
  .header p { font-size: 8pt; color: #555; }
  .vacancy-info { margin-bottom: 10px; }
  .vacancy-info td { border: none; padding: 1px 4px; font-size: 8pt; }
  .vacancy-info .lbl { font-weight: bold; width: 110px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  td, th { border: 1px solid #000; padding: 3px 4px; vertical-align: middle; text-align: center; font-size: 7.5pt; }
  th { background: #2a338f; color: #fff; font-weight: bold; }
  .left { text-align: left; }
  .section-title { background: #2a338f; color: #fff; font-weight: bold; font-size: 8pt; padding: 3px 6px; text-align: left; }
  .signature-area { margin-top: 30px; }
  .sig-line { display: inline-block; width: 45%; margin: 0 2%; text-align: center; }
  .sig-line .line { border-top: 1px solid #000; margin-top: 40px; padding-top: 4px; font-size: 7.5pt; font-weight: bold; }
  .sig-line .title { font-size: 7pt; color: #555; }
  .footer { margin-top: 15px; font-size: 7pt; color: #555; text-align: center; }
  .page-break { page-break-before: always; }
  .badge-qualified { color: #fff; background: #15803d; padding: 1px 4px; border-radius: 2px; font-size: 6.5pt; font-weight: bold; }
  .badge-disqualified { color: #fff; background: #b91c1c; padding: 1px 4px; border-radius: 2px; font-size: 6.5pt; font-weight: bold; }
  .eopt-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin: 0 1px; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <h1>Republic of the Philippines</h1>
    <h2>Civil Service Commission</h2>
    <h2>Regional Office No. VIII</h2>
    <h1>COMPARATIVE ASSESSMENT RESULT</h1>
    <p>For Endorsement to the Appointing Authority</p>
  </div>

  <table class="vacancy-info">
    <tr><td class="lbl">Position Title:</td><td>{{ $vacancy->position_title ?? '—' }}</td></tr>
    <tr><td class="lbl">Plantilla Item No.:</td><td>{{ $vacancy->plantilla_no ?? '—' }}</td></tr>
    <tr><td class="lbl">Salary Grade:</td><td>{{ $vacancy->salary_grade ?? '—' }}</td></tr>
    <tr><td class="lbl">Place of Assignment:</td><td>{{ $vacancy->place_of_assignment ?? '—' }}</td></tr>
    <tr><td class="lbl">Position Level:</td><td>{{ $vacancy->position_level ?? '—' }}</td></tr>
  </table>

  <table>
    <thead>
      <tr>
        <th width="24">#</th>
        <th width="100" class="left">Applicant</th>
        <th width="26">QS</th>
        <th width="28">TWE</th>
        <th width="28">CBWE</th>
        <th width="26">BEI</th>
        <th width="26">BI</th>
        <th width="80">EOPT</th>
        <th width="58" class="left">Deliberation Action</th>
        <th width="20">Rk</th>
        <th class="left">Remarks</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($applications as $i => $app)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td class="left">{{ $app->token ?? '—' }}</td>
        <td>
          @if ($app->qs_result === 'qualified')
            <span class="badge-qualified">Q</span>
          @elseif ($app->qs_result === 'disqualified')
            <span class="badge-disqualified">DQ</span>
          @else
            —
          @endif
        </td>
        <td>{{ optional($app->examScores->firstWhere('exam_type', 'TWE'))->percentage ?? '—' }}</td>
        <td>{{ optional($app->examScores->firstWhere('exam_type', 'CBWE'))->percentage ?? '—' }}</td>
        <td>{{ $app->bei_average ?? '—' }}</td>
        <td>
          @php $biReport = $app->backgroundInvestigationReports->first(); @endphp
          {{ $biReport && $biReport->submitted_at ? '✓' : '—' }}
        </td>
        <td style="font-size: 6.5pt;">
          @if ($app->eoptResults->isNotEmpty())
            @php $ratings = ['emotional_stability', 'extraversion', 'openness_to_experience', 'agreeableness', 'conscientiousness']; @endphp
            @foreach ($ratings as $cat)
              @php $r = $app->eoptResults->first()->{$cat} ?? null; @endphp
              @if ($r)
                <span class="eopt-dot" style="background:
                  {{ match($r) { 'very_high' => '#059669', 'high' => '#16a34a', 'average' => '#f59e0b', 'low' => '#ea580c', 'very_low' => '#dc2626', default => '#d1d5db' } }}"></span>
              @else
                <span class="eopt-dot" style="background: #d1d5db;"></span>
              @endif
            @endforeach
          @else
            —
          @endif
        </td>
        <td class="left">{{ optional($app->deliberationResult)->action ?? '—' }}</td>
        <td>{{ optional($app->deliberationResult)->rank ?? '—' }}</td>
        <td class="left">{{ optional($app->deliberationResult)->remarks ?? '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="11" style="text-align: center; color: #999;">No applicants.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="signature-area">
    <div class="sig-line">
      <div class="line">Chairperson, HRMPSB</div>
      <div class="title">Director III</div>
    </div>
    <div class="sig-line">
      <div class="line">Member, HRMPSB</div>
      <div class="title">&nbsp;</div>
    </div>
  </div>
  <div style="margin-top: 10px; text-align: center;">
    <div class="sig-line">
      <div class="line">Member, HRMPSB</div>
      <div class="title">&nbsp;</div>
    </div>
    <div class="sig-line">
      <div class="line">HRMPSB Secretariat</div>
      <div class="title">&nbsp;</div>
    </div>
  </div>

  <div class="footer">
    Generated by CSC RO VIII - Recruitment Portal &bull; {{ $generatedAt->format('F d, Y h:i A') }}
  </div>
</div>
</body>
</html>
