<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\QSEvaluationsLocked;
use App\Services\AuditLog;

class LogQSEvaluationsLocked
{
    public function handle(QSEvaluationsLocked $event): void
    {
        AuditLog::record('qs_evaluations_locked', $event->vacancy);
    }
}
