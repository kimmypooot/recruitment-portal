<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackgroundInvestigationReport extends Model
{
    protected $fillable = [
        'application_id',
        'investigator_name',
        'investigator_email',
        'token',
        'token_expires_at',
        'file_path',
        'original_filename',
        'on_competencies',
        'on_performance',
        'submitted_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'submitted_at'     => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->isPast();
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
