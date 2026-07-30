<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Notifications\ApplicationStatusUpdated as StatusNotification;
use App\Notifications\ShortlistResult;
use Illuminate\Support\Facades\Notification;

class SendApplicationStatusUpdatedNotification
{
    public function handle(ApplicationStatusUpdated $event): void
    {
        $user = $event->application->applicant?->user;
        if (! $user) {
            return;
        }

        Notification::send($user, new StatusNotification(
            $event->application,
            $event->oldStatus,
            $event->newStatus,
            $event->silent,
        ));

        if ($event->newStatus === 'shortlisted') {
            Notification::send($user, new ShortlistResult($event->application, true));
        } elseif ($event->newStatus === 'disqualified') {
            Notification::send($user, new ShortlistResult($event->application, false));
        }
    }
}
