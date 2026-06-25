<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShortlistResult extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly bool $isShortlisted,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $positionTitle = $this->application->vacancy?->position_title ?? 'a position';
        $profile       = $this->application->applicant;
        $name          = $profile ? trim("{$profile->first_name} {$profile->last_name}") : 'Applicant';

        if ($this->isShortlisted) {
            return (new MailMessage)
                ->subject("Good News — You Have Been Shortlisted!")
                ->greeting("Dear {$name},")
                ->line("Congratulations! We are pleased to inform you that you have been shortlisted for the position of **{$positionTitle}**.")
                ->line('You will receive further instructions regarding the next steps of the selection process. Please keep an eye on your notifications and email.')
                ->action('View Your Application', url('/applicant/applications'))
                ->line('We look forward to meeting you in the next stage of the process.')
                ->salutation('CSC RO VIII Recruitment Portal');
        }

        return (new MailMessage)
            ->subject("Update on Your Application — {$positionTitle}")
            ->greeting("Dear {$name},")
            ->line("Thank you for your interest in the position of **{$positionTitle}**.")
            ->line('After careful evaluation, we regret to inform you that you were not selected for the shortlist at this time.')
            ->line('We encourage you to apply again for future vacancies that match your qualifications. Thank you for your understanding.')
            ->action('Browse Other Vacancies', url('/'))
            ->salutation('CSC RO VIII Recruitment Portal');
    }

    public function toArray(object $notifiable): array
    {
        $positionTitle = $this->application->vacancy?->position_title ?? 'a position';

        if ($this->isShortlisted) {
            return [
                'application_id' => $this->application->id,
                'position'       => $positionTitle,
                'message'        => "Congratulations! You have been shortlisted for {$positionTitle}.",
            ];
        }

        return [
            'application_id' => $this->application->id,
            'position'       => $positionTitle,
            'message'        => "We regret to inform you that you were not shortlisted for {$positionTitle}.",
        ];
    }
}
