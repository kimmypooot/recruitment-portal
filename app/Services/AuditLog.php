<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        } catch (\Throwable) {
            // Silently fail so audit logging never breaks core flows
        }
    }
}
