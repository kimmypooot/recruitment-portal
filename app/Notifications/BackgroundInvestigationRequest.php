<?php

namespace App\Notifications;

use App\Models\BackgroundInvestigationReport;
use App\Services\EmailTemplateMailBuilder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackgroundInvestigationRequest extends Notification
{
    public function __construct(
        private readonly BackgroundInvestigationReport $report,
        private readonly string $positionTitle,
        private readonly string $applicantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/background-investigation/upload/{$this->report->token}");

        $expiry = $this->report->token_expires_at
            ? $this->report->token_expires_at->format('F j, Y \a\t g:i A')
            : '30 days';

        return EmailTemplateMailBuilder::build('background_investigation_request', [
            '{{investigator_name}}' => $this->report->investigator_name,
            '{{applicant_name}}' => $this->applicantName,
            '{{position_title}}' => $this->positionTitle,
            '{{expiry}}' => $expiry,
        ], $url);
    }
}
