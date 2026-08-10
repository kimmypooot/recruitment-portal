<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Section VI item 29 of CS Form No. 212 (Revised 2026). */
class VoluntaryWork extends Model
{
    protected $fillable = [
        'applicant_profile_id', 'organization_name_address',
        'date_from', 'date_to', 'number_of_hours',
        'position_nature_of_work', 'sort_order',
    ];

    protected $casts = [
        'date_from'       => 'date',
        'date_to'         => 'date',
        'number_of_hours' => 'decimal:2',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
