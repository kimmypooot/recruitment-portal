<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Application;
use App\Services\AuditLog;

class ApplicationObserver
{
    /**
     * Previously wrote directly to the AuditLog Eloquent model with a column
     * set (model_type/model_id/old_values/new_values/ip_address/user_agent)
     * that doesn't exist on the audit_logs table at all — every real column
     * on that table (user_id/action/auditable_type/auditable_id) was also
     * blocked by the model's missing $fillable. Since this observer is
     * registered globally on Application, any ->update() that changed an
     * attribute — QS/CBWE/BEI/background-check locking, deliberation and
     * appointing-authority decisions, withdraw, updateStatus — fatally threw
     * a MassAssignmentException before this fix.
     *
     * Routed through the App\Services\AuditLog helper every controller
     * already uses, which matches the real schema and fails safe (logs a
     * warning rather than throwing) instead of taking down the request.
     */
    public function updated(Application $application): void
    {
        if (! empty($application->getChanges())) {
            AuditLog::record('application_updated', $application);
        }
    }
}
