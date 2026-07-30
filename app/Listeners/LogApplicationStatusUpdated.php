<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Services\AuditLog;

class LogApplicationStatusUpdated
{
    public function handle(ApplicationStatusUpdated $event): void
    {
        $action = "application_status_changed:{$event->oldStatus}→{$event->newStatus}";
        AuditLog::record($action, $event->application);
    }
}
