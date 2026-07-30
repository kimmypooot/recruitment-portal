<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VacancyStateChanged;
use App\Services\AuditLog;

class LogVacancyStateChanged
{
    public function handle(VacancyStateChanged $event): void
    {
        AuditLog::record($event->action, $event->vacancy);
    }
}
