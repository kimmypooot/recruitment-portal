<?php

namespace App\Notifications;

use App\Models\InterviewSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private readonly InterviewSchedule $schedule,
        private readonly string $positionTitle,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->schedule->scheduled_at->format('F j, Y');
        $time = $this->schedule->scheduled_at->format('g:i A');

        return (new MailMessage)
            ->subject("Behavioral Event Interview (BEI) Schedule — {$this->positionTitle}")
            ->greeting("Dear {$notifiable->name},")
            ->line('Congratulations! You have passed the written examinations and are hereby invited to undergo the **Behavioral Event Interview (BEI)** for the following position:')
            ->line("**{$this->positionTitle}**")
            ->line('Please take note of your interview schedule:')
            ->line("**Date:** {$date}")
            ->line("**Time:** {$time}")
            ->line("**Venue:** {$this->schedule->venue}")
            ->when(
                $this->schedule->notes,
                fn ($m) => $m->line("**Notes / Panel Information:** {$this->schedule->notes}")
            )
            ->line('Please report to the venue at least 15 minutes before your scheduled time. Bring a valid government-issued ID and any supporting documents you may wish to present.')
            ->action('View My Application', url('/applicant/applications'))
            ->line('For inquiries, please contact the HR Management and Practices Section.')
            ->salutation('Civil Service Commission Regional Office VIII');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'bei_scheduled',
            'scheduled_at' => $this->schedule->scheduled_at->toIso8601String(),
            'venue'        => $this->schedule->venue,
            'position'     => $this->positionTitle,
        ];
    }
}
