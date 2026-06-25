<?php

namespace App\Notifications;

use App\Models\ExamSchedule;
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

        return (new MailMessage)
            ->subject("Exam Scheduled — {$this->positionTitle}")
            ->greeting("Dear {$notifiable->name},")
            ->line("Congratulations! You have qualified for the written examination for the position of **{$this->positionTitle}**.")
            ->line('Here are the details of your exam:')
            ->line("- **Type:** {$examLabel}")
            ->line("- **Date:** {$date}")
            ->line("- **Time:** {$time}")
            ->line("- **Venue:** {$this->schedule->venue}")
            ->when(
                $this->schedule->notes,
                fn ($m) => $m->line("- **Reminders:** {$this->schedule->notes}")
            )
            ->line('Please arrive at least 15 minutes early. Bring a valid government-issued ID and your own ballpen.')
            ->action('View My Application', url('/applicant/applications'))
            ->line('For questions, please contact the HR Management and Practices Section.')
            ->salutation('CSC RO VIII Recruitment Portal');
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
