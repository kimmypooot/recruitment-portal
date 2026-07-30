<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Services\AuditLog;

class LogApplicationSubmitted
{
    public function handle(ApplicationSubmitted $event): void
    {
        AuditLog::record('application_submitted', $event->application);
    }
}
