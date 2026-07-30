<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Notifications\ApplicationSubmitted as ApplicationSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class SendApplicationSubmittedNotification
{
    public function handle(ApplicationSubmitted $event): void
    {
        $user = $event->application->applicant?->user;
        if ($user) {
            Notification::send($user, new ApplicationSubmittedNotification($event->application));
        }
    }
}
