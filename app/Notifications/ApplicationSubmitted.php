<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use App\Services\EmailTemplateMailBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $positionTitle = $this->application->vacancy?->position_title ?? 'a position';

        $mail = EmailTemplateMailBuilder::build('application_submitted', [
            '{{position_title}}' => $positionTitle,
            '{{reference_code}}' => $this->application->reference_code,
        ]);

        // Always included, independent of the admin-configurable template
        // body — this is the applicant's only copy of the code needed for
        // the passwordless "Track My Application" lookup, so it can't be
        // dropped by editing the template.
        return $mail->line('')
            ->line("Reference code: **{$this->application->reference_code}**")
            ->line('Use this with your email at any time to check your status without signing in.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'position' => $this->application->vacancy?->position_title ?? '—',
            'message' => 'Your application has been submitted successfully. We will review it and get back to you.',
        ];
    }
}
