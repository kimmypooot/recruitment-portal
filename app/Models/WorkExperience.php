<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkExperience extends Model
{
    protected $fillable = [
        'applicant_profile_id', 'position_title', 'department_agency',
        'monthly_salary', 'salary_grade', 'appointment_status',
        'government_service', 'date_from', 'date_to', 'is_present',
    ];

    protected $casts = [
        'government_service' => 'boolean',
        'is_present'         => 'boolean',
        'monthly_salary'     => 'decimal:2',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
