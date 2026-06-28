<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentSelectedByAa extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly Vacancy $vacancy,
        private readonly string $applicantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'vacancy_id'     => $this->vacancy->id,
            'vacancy_title'  => $this->vacancy->position_title,
            'applicant_name' => $this->applicantName,
            'action'         => 'appointed',
            'message'        => "The Appointing Authority has selected {$this->applicantName} for {$this->vacancy->position_title}.",
        ];
    }
}
