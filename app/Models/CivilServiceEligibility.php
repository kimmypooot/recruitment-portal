<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Section IV item 27 of CS Form No. 212 (Revised 2026). */
class CivilServiceEligibility extends Model
{
    protected $fillable = [
        'applicant_profile_id', 'eligibility_name', 'rating',
        'examination_date', 'examination_place',
        'license_number', 'license_valid_until', 'sort_order',
    ];

    protected $casts = [
        'examination_date'    => 'date',
        'license_valid_until' => 'date',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
