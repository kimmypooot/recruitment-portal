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

        return EmailTemplateMailBuilder::build('application_submitted', [
            '{{position_title}}' => $positionTitle,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'position'       => $this->application->vacancy?->position_title ?? '—',
            'message'        => 'Your application has been submitted successfully. We will review it and get back to you.',
        ];
    }
}
