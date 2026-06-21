<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsForm extends Model
{
    public const TYPES = [
        '33A'   => 'CS Form 33-A (Appointment)',
        '33B'   => 'CS Form 33-B (Appointment — Casual/Contractual)',
        'form1' => 'CS Form 1 (Personal Data Sheet, Revised 2025)',
    ];

    protected $fillable = [
        'application_id', 'form_type', 'file_path',
        'generated_by', 'generated_at', 'signed_at', 'submitted_to_csc_at',
    ];

    protected $casts = [
        'generated_at'       => 'datetime',
        'signed_at'          => 'datetime',
        'submitted_to_csc_at'=> 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    public function isSubmittedToCsc(): bool
    {
        return $this->submitted_to_csc_at !== null;
    }
}
