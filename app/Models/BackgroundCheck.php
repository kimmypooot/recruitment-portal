<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackgroundCheck extends Model
{
    protected $fillable = [
        'application_id', 'checked_by', 'employment_verified', 'education_verified',
        'character_ref_verified', 'nbi_clearance', 'background_result', 'remarks',
        'checked_at', 'locked_at',
    ];

    protected $casts = [
        'employment_verified'    => 'boolean',
        'education_verified'     => 'boolean',
        'character_ref_verified' => 'boolean',
        'nbi_clearance'          => 'boolean',
        'checked_at'             => 'datetime',
        'locked_at'              => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
