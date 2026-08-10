<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Page 4 item 41 of CS Form No. 212 (Revised 2026). The form prints exactly
 * three rows; the count is enforced at the request layer, not here.
 */
class PdsReference extends Model
{
    public const REQUIRED_COUNT = 3;

    protected $fillable = [
        'applicant_profile_id', 'name',
        'office_residential_address', 'contact_no_or_email', 'sort_order',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
