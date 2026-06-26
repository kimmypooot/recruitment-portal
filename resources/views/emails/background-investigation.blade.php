<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; -webkit-font-smoothing: antialiased; }
  </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:'Poppins',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;-webkit-font-smoothing:antialiased;">

  <!-- Outer table -->
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:30px 10px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

          <!-- Top bar -->
          <tr>
            <td style="background:linear-gradient(90deg,#2a338f,#1a5276);border-radius:12px 12px 0 0;padding:20px 30px;text-align:center;">
              <h1 style="color:#ffffff;font-size:18px;font-weight:600;margin:0;letter-spacing:0.3px;">CIVIL SERVICE COMMISSION</h1>
              <p style="color:#c5cae9;font-size:12px;margin:2px 0 0 0;">CSC RO VIII - Recruitment Portal</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="background-color:#ffffff;padding:35px 30px;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-bottom:20px;border-bottom:2px solid #2a338f;">
                    <p style="font-size:14px;color:#374151;margin:0;font-weight:500;">Dear <strong style="color:#2a338f;">{{ $report->investigator_name }}</strong>,</p>
                  </td>
                </tr>

                <!-- Intro -->
                <tr>
                  <td style="padding:20px 0 15px 0;">
                    <p style="font-size:14px;color:#4b5563;line-height:1.7;margin:0;">
                      You have been requested to conduct a background investigation for the following applicant:
                    </p>
                  </td>
                </tr>

                <!-- Details card -->
                <tr>
                  <td style="padding:0 0 20px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f9fb;border-radius:10px;border-left:4px solid #2a338f;">
                      <tr>
                        <td style="padding:18px 22px;">
                          <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                              <td style="padding-bottom:8px;">
                                <span style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Applicant</span>
                                <p style="font-size:15px;color:#111827;font-weight:600;margin:2px 0 0 0;">{{ $applicantName }}</p>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <span style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Position Applied</span>
                                <p style="font-size:15px;color:#111827;font-weight:500;margin:2px 0 0 0;">{{ $positionTitle }}</p>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- Instructions -->
                <tr>
                  <td style="padding:0 0 18px 0;">
                    <p style="font-size:14px;color:#4b5563;line-height:1.7;margin:0;">
                      Please complete the background investigation report by uploading the signed PDF and providing your assessments. Your report must include:
                    </p>
                  </td>
                </tr>

                <!-- Checklist -->
                <tr>
                  <td style="padding:0 0 22px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                      @foreach([
                        ['icon' => '📄', 'text' => 'The completed & signed Background Investigation PDF'],
                        ['icon' => '✍️', 'text' => 'Assessment on Competencies (minimum 1,000 characters)'],
                        ['icon' => '📊', 'text' => 'Assessment on Performance & Other Relevant Information (minimum 1,000 characters)'],
                      ] as $item)
                      <tr>
                        <td style="padding:5px 0;">
                          <table cellpadding="0" cellspacing="0">
                            <tr>
                              <td style="padding-right:10px;font-size:14px;vertical-align:top;">{{ $item['icon'] }}</td>
                              <td style="font-size:13px;color:#4b5563;line-height:1.6;">{{ $item['text'] }}</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      @endforeach
                    </table>
                  </td>
                </tr>

                <!-- CTA Button -->
                <tr>
                  <td align="center" style="padding:0 0 22px 0;">
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="border-radius:8px;background:linear-gradient(135deg,#2a338f,#1e2570);padding:0;">
                          <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:14px 36px;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;letter-spacing:0.3px;">
                            Upload Background Investigation Report
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- Expiry -->
                <tr>
                  <td style="padding:0 0 15px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff5f5;border-radius:8px;border:1px solid #fecaca;">
                      <tr>
                        <td style="padding:12px 16px;">
                          <table cellpadding="0" cellspacing="0">
                            <tr>
                              <td style="padding-right:8px;vertical-align:middle;color:#dc2626;font-size:16px;">⏰</td>
                              <td style="font-size:13px;color:#991b1b;line-height:1.5;">
                                This link will expire on <strong>{{ $expiry }}</strong>. Please submit your report before the deadline.
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- Footer text -->
                <tr>
                  <td style="padding:10px 0 0 0;border-top:1px solid #e5e7eb;">
                    <p style="font-size:13px;color:#6b7280;line-height:1.7;margin:0 0 3px 0;">
                      For questions, please contact the <strong>HRMPSB Secretariat</strong>.
                    </p>
                    <p style="font-size:13px;color:#2a338f;font-weight:600;margin:0;">
                      CSC RO VIII - Recruitment Portal
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer bar -->
          <tr>
            <td style="background-color:#2a338f;border-radius:0 0 12px 12px;padding:16px 30px;text-align:center;">
              <p style="font-size:11px;color:#a5b4fc;margin:0;line-height:1.6;">
                This is an automated message from the CSC RO VIII - Recruitment Portal.Please do not reply to this email.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
