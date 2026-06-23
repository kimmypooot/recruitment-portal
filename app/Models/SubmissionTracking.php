<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionTracking extends Model
{
    protected $table = 'submission_tracking';

    protected $fillable = [
        'vacancy_id', 'application_id', 'deadline_type',
        'due_at', 'submitted_at', 'status', 'last_notified_at',
    ];

    protected $casts = [
        'due_at'           => 'datetime',
        'submitted_at'     => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && now()->isAfter($this->due_at);
    }

    public function daysRemaining(): int
    {
        return (int) now()->diffInDays($this->due_at, false);
    }
}
