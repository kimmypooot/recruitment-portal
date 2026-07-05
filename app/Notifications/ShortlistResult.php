<?php

namespace App\Notifications;

use App\Models\Application;
use App\Services\EmailTemplateMailBuilder;
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

        $key = $this->isShortlisted ? 'shortlist_result_shortlisted' : 'shortlist_result_not_shortlisted';

        return EmailTemplateMailBuilder::build($key, [
            '{{name}}' => $name,
            '{{position_title}}' => $positionTitle,
        ]);
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
