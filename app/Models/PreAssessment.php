<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreAssessment extends Model
{
    protected $fillable = [
        'application_id',
        'pds_submitted', 'ipcr_submitted', 'tor_submitted', 'coe_submitted',
        'pds_remarks', 'ipcr_remarks', 'tor_remarks', 'coe_remarks',
        'requirements_complete', 'requirements_remarks', 'secretariat_remarks',
        'license_remarks',
        'education_meets', 'license_meets', 'experience_meets', 'training_meets', 'eligibility_meets',
        'hrd_assessment', 'hrd_remarks',
        'consensus',
        'assessed_by', 'assessed_at',
    ];

    protected $casts = [
        'pds_submitted'         => 'boolean',
        'ipcr_submitted'        => 'boolean',
        'tor_submitted'         => 'boolean',
        'coe_submitted'         => 'boolean',
        'requirements_complete' => 'boolean',
        'education_meets'       => 'boolean',
        'license_meets'         => 'boolean',
        'experience_meets'      => 'boolean',
        'training_meets'        => 'boolean',
        'eligibility_meets'     => 'boolean',
        'hrd_assessment'        => 'boolean',
        'consensus'             => 'boolean',
        'assessed_at'           => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
