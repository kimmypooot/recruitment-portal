<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrmbsboardComposition extends Model
{
    protected $table = 'hrmpsb_compositions';

    protected $fillable = [
        'user_id', 'hrmpsb_role', 'member_type',
        'is_active', 'assigned_by', 'assigned_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public const ROLES = [
        'chairperson'                   => 'Chairperson',
        'secretariat'                   => 'HRMPSB Secretariat',
        'director-representative'       => 'Director II Representative',
        'division-chief-representative' => 'Division Chief Representative',
        'hr-chief'                      => 'Chief of HR Division',
        'head-of-unit'                  => 'Head of Unit',
        'pintig-representative-1st'     => 'PINTIG Representative (1st Level)',
        'pintig-representative-2nd'     => 'PINTIG Representative (2nd Level)',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
