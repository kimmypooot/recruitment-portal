<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    // Mirrors the actual audit_logs schema — see 2026_06_19_132300_create_audit_logs_table.
    // (App\Services\AuditLog::record() is the write path every controller actually
    // uses; this Eloquent model exists for reads/relations.)
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
