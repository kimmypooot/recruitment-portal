<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Section II item 23 of CS Form No. 212 (Revised 2026). */
class PdsChild extends Model
{
    protected $fillable = ['applicant_profile_id', 'full_name', 'date_of_birth', 'sort_order'];

    protected $casts = ['date_of_birth' => 'date'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
