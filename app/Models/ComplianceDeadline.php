<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDeadline extends Model
{
    protected $fillable = [
        'vacancy_id', 'deadline_type', 'due_at', 'completed_at', 'notified_at',
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
        'notified_at'  => 'datetime',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function isOverdue(): bool
    {
        return $this->completed_at === null && $this->due_at->isPast();
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->diffInDays($this->due_at, false));
    }
}
