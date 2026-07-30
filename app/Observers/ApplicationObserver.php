<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Application;
use App\Models\AuditLog;

class ApplicationObserver
{
    public function updated(Application $application): void
    {
        if (! empty($application->getChanges())) {
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'updated',
                'model_type' => Application::class,
                'model_id'   => $application->id,
                'old_values' => json_encode($application->getOriginal()),
                'new_values' => json_encode($application->getChanges()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
