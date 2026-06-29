<?php

namespace App\Notifications;

use App\Models\BackgroundInvestigationReport;
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

        return (new MailMessage)
            ->subject("Background Investigation Request — {$this->positionTitle}")
            ->greeting("Dear {$this->report->investigator_name},")
            ->line('You have been requested to conduct a background investigation for the following applicant:')
            ->line("**Applicant:** {$this->applicantName}")
            ->line("**Position Applied:** {$this->positionTitle}")
            ->line('Please complete the background investigation report by uploading the signed PDF and providing your assessments. Your report must include:')
            ->line('- The completed and signed Background Investigation PDF')
            ->line('- Assessment on Competencies (minimum 1,000 characters)')
            ->line('- Assessment on Performance and Other Relevant Information (minimum 1,000 characters)')
            ->action('Upload Background Investigation Report', $url)
            ->line("This link will expire on **{$expiry}**. Please submit your report before the deadline.")
            ->salutation('CSC RO VIII - Recruitment Portal');
    }
}
