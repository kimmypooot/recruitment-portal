<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Training extends Model
{
    protected $fillable = [
        'applicant_profile_id', 'title', 'date_from', 'date_to',
        'hours', 'ld_type', 'conducted_by',
    ];

    protected $casts = ['hours' => 'decimal:2'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
