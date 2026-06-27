<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliberationResult extends Model
{
    protected $fillable = [
        'vacancy_id', 'application_id', 'action', 'rank', 'decided_by', 'decided_at', 'remarks', 'locked_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'locked_at'  => 'datetime',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
