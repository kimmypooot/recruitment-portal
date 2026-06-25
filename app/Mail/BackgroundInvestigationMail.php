<?php

namespace App\Mail;

use App\Models\BackgroundInvestigationReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackgroundInvestigationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly BackgroundInvestigationReport $report,
        private readonly string $positionTitle,
        private readonly string $applicantName,
    ) {
        $this->to($report->investigator_email, $report->investigator_name);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Background Investigation Request — {$this->positionTitle}",
        );
    }

    public function content(): Content
    {
        $url = url("/background-investigation/upload/{$this->report->token}");

        $expiry = $this->report->token_expires_at
            ? $this->report->token_expires_at->format('F j, Y \a\t g:i A')
            : '30 days';

        return new Content(
            view: 'emails.background-investigation',
            with: [
                'report' => $this->report,
                'url' => $url,
                'expiry' => $expiry,
                'positionTitle' => $this->positionTitle,
                'applicantName' => $this->applicantName,
            ],
        );
    }
}
