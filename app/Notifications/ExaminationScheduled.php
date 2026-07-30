<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ExamSchedule;
use App\Services\EmailTemplateMailBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExaminationScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private readonly ExamSchedule $schedule,
        private readonly string $positionTitle,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $examLabel = match ($this->schedule->exam_type) {
            'TWE'  => 'Technical Written Examination',
            'CBWE' => 'Competency-Based Written Examination',
            default => $this->schedule->exam_type,
        };

        $date = $this->schedule->scheduled_at->format('F j, Y');
        $time = $this->schedule->scheduled_at->format('g:i A');

        return EmailTemplateMailBuilder::build('exam_scheduled', [
            '{{first_name}}' => $notifiable->first_name,
            '{{position_title}}' => $this->positionTitle,
            '{{exam_type}}' => $examLabel,
            '{{date}}' => $date,
            '{{time}}' => $time,
            '{{venue}}' => $this->schedule->venue,
            '{{notes_block}}' => $this->schedule->notes ? "- **Reminders:** {$this->schedule->notes}" : '',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        $examLabel = match ($this->schedule->exam_type) {
            'TWE'  => 'Technical Written Examination',
            'CBWE' => 'Competency-Based Written Examination',
            default => $this->schedule->exam_type,
        };

        $date = $this->schedule->scheduled_at->format('F j, Y');
        $time = $this->schedule->scheduled_at->format('g:i A');

        return [
            'type'         => 'exam_scheduled',
            'exam_type'    => $this->schedule->exam_type,
            'scheduled_at' => $this->schedule->scheduled_at->toIso8601String(),
            'venue'        => $this->schedule->venue,
            'position'     => $this->positionTitle,
            'message'      => "You have an exam scheduled for {$this->positionTitle} on {$date} at {$time}.",
        ];
    }
}
