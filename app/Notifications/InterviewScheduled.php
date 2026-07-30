<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\InterviewSchedule;
use App\Services\EmailTemplateMailBuilder;
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

        return EmailTemplateMailBuilder::build('interview_scheduled', [
            '{{first_name}}' => $notifiable->first_name,
            '{{position_title}}' => $this->positionTitle,
            '{{date}}' => $date,
            '{{time}}' => $time,
            '{{venue}}' => $this->schedule->venue,
            '{{notes_block}}' => $this->schedule->notes ? "- **Additional Info:** {$this->schedule->notes}" : '',
        ]);
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
