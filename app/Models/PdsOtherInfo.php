<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Section VIII items 31-33 of CS Form No. 212 (Revised 2026) — three parallel
 * repeating lists sharing one table, discriminated by `kind`.
 */
class PdsOtherInfo extends Model
{
    public const KIND_SPECIAL_SKILL_HOBBY      = 'special_skill_hobby';       // 31
    public const KIND_NON_ACADEMIC_DISTINCTION = 'non_academic_distinction';  // 32
    public const KIND_MEMBERSHIP               = 'membership';                // 33

    public const KINDS = [
        self::KIND_SPECIAL_SKILL_HOBBY,
        self::KIND_NON_ACADEMIC_DISTINCTION,
        self::KIND_MEMBERSHIP,
    ];

    protected $fillable = ['applicant_profile_id', 'kind', 'value', 'sort_order'];

    public function scopeOfKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
