<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Section II items 22, 24, 25 of CS Form No. 212 (Revised 2026). */
class PdsFamilyBackground extends Model
{
    protected $fillable = [
        'applicant_profile_id',
        'spouse_surname', 'spouse_first_name', 'spouse_middle_name', 'spouse_name_extension',
        'spouse_occupation', 'spouse_employer', 'spouse_business_address', 'spouse_telephone',
        'father_surname', 'father_first_name', 'father_middle_name', 'father_name_extension',
        'mother_maiden_surname', 'mother_maiden_first_name', 'mother_maiden_middle_name',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
