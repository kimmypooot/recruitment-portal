<?php

namespace App\Notifications;

use App\Mail\BackgroundInvestigationMail;
use App\Models\BackgroundInvestigationReport;
use Illuminate\Notifications\Notification;

class BackgroundInvestigationRequest extends Notification
{
    public function __construct(
        private readonly BackgroundInvestigationReport $report,
        private readonly string $positionTitle,
        private readonly string $applicantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): BackgroundInvestigationMail
    {
        return new BackgroundInvestigationMail(
            $this->report,
            $this->positionTitle,
            $this->applicantName,
        );
    }
}
