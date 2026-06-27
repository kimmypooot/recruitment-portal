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
            ->subject("Interview Scheduled — {$this->positionTitle}")
            ->greeting("Dear {$notifiable->first_name},")
            ->line("Congratulations on passing the written exam! You are invited to a **Behavioral Event Interview (BEI)** for the position of **{$this->positionTitle}**.")
            ->line('Here are the details of your interview:')
            ->line("- **Date:** {$date}")
            ->line("- **Time:** {$time}")
            ->line("- **Venue:** {$this->schedule->venue}")
            ->when(
                $this->schedule->notes,
                fn ($m) => $m->line("- **Additional Info:** {$this->schedule->notes}")
            )
            ->line('Please arrive at least 15 minutes early. Bring a valid government-issued ID and any supporting documents you wish to present.')
            ->action('View My Application', url('/applicant/applications'))
            ->line('For questions, please contact the HR Management and Practices Section.')
            ->salutation('CSC RO VIII - Recruitment Portal');
    }

    public function toArray(object $notifiable): array
    {
        $date = $this->schedule->scheduled_at->format('F j, Y');
        $time = $this->schedule->scheduled_at->format('g:i A');

        return [
            'type'         => 'bei_scheduled',
            'scheduled_at' => $this->schedule->scheduled_at->toIso8601String(),
            'venue'        => $this->schedule->venue,
            'position'     => $this->positionTitle,
            'message'      => "You have an interview scheduled for {$this->positionTitle} on {$date} at {$time}.",
        ];
    }
}
