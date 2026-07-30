<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrmpsbComposition extends Model
{
    protected $table = 'hrmpsb_compositions';

    protected $fillable = [
        'user_id', 'hrmpsb_role', 'member_type',
        'is_active', 'assigned_by', 'assigned_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    // Statically assignable roles (global, stored in hrmpsb_compositions)
    public const ROLES = [
        'chairperson' => 'Chairperson',
        'secretariat' => 'HRMPSB Secretariat',
        'appointing-authority' => 'Appointing Authority',
        'director-representative' => 'Director II Representative',
        'division-chief-representative' => 'Division Chief Representative',
        'hr-chief' => 'Chief of HR Division',
        'pintig-representative-1st' => 'PINTIG Representative (1st Level)',
        'pintig-representative-2nd' => 'PINTIG Representative (2nd Level)',
    ];

    // Dynamically resolved per-vacancy (based on place_of_assignment)
    public const DYNAMIC_ROLES = [
        'head-of-unit' => 'Head of Unit',
    ];

    // All roles combined (static + dynamic) for display/composition building
    public const ALL_ROLES = self::ROLES + self::DYNAMIC_ROLES;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
