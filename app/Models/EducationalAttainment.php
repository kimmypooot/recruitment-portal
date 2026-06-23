<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationalAttainment extends Model
{
    protected $fillable = [
        'applicant_profile_id', 'level', 'school_name', 'degree_course',
        'period_from', 'period_to', 'units_earned', 'year_graduated', 'honors',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
