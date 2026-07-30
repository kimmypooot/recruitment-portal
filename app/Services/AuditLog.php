<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLog
{
    public static function record(string $action, Model $model): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id'      => Auth::id(),
                'action'       => $action,
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break core flows, but leave a trace — silent audit loss is
            // itself a compliance problem.
            Log::warning('Audit log write failed', [
                'action' => $action,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
