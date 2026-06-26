<?php

namespace App\Notifications;

use App\Models\Application;
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

        return (new MailMessage)
            ->subject("Application Received — {$positionTitle}")
            ->greeting('Dear Applicant,')
            ->line("Thank you for applying for the position of **{$positionTitle}**.")
            ->line('Your application has been received and is now being reviewed. We will notify you once there is an update on your status.')
            ->action('View Your Application', url('/applicant/applications'))
            ->line("If you have any questions, feel free to reach out to the CSC RO VIII.")
            ->salutation('CSC RO VIII - Recruitment Portal');
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
