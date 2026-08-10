<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Page 4 questions 34-42 of CS Form No. 212 (Revised 2026).
 *
 * Every yes/no is nullable: "answered No" and "not yet answered" are different
 * states, and completeness checking depends on the difference.
 */
class PdsDeclaration extends Model
{
    /**
     * The yes/no items that must all be answered before the PDS is complete.
     * Note 34a/34b are both required; the rest are single questions.
     */
    public const REQUIRED_ANSWERS = [
        'q34a_related_third_degree',
        'q34b_related_fourth_degree',
        'q35a_guilty_admin_offense',
        'q35b_criminally_charged',
        'q36_convicted',
        'q37_separated_from_service',
        'q38a_election_candidate',
        'q38b_resigned_to_campaign',
        'q39_immigrant_status',
        'q40a_indigenous_group',
        'q40b_pwd',
        'q40c_solo_parent',
    ];

    protected $fillable = [
        'applicant_profile_id',
        'q34a_related_third_degree', 'q34b_related_fourth_degree', 'q34_details',
        'q35a_guilty_admin_offense', 'q35a_details',
        'q35b_criminally_charged', 'q35b_date_filed', 'q35b_case_status',
        'q36_convicted', 'q36_details',
        'q37_separated_from_service', 'q37_details',
        'q38a_election_candidate', 'q38a_details',
        'q38b_resigned_to_campaign', 'q38b_details',
        'q39_immigrant_status', 'q39_country',
        'q40a_indigenous_group', 'q40a_group_name',
        'q40b_pwd', 'q40b_id_no',
        'q40c_solo_parent', 'q40c_id_no',
        'government_id_type', 'government_id_number',
        'government_id_issuance_date', 'government_id_issuance_place',
        'date_accomplished', 'signature_path', 'right_thumbmark_path', 'attested_at',
    ];

    protected $casts = [
        'q34a_related_third_degree'   => 'boolean',
        'q34b_related_fourth_degree'  => 'boolean',
        'q35a_guilty_admin_offense'   => 'boolean',
        'q35b_criminally_charged'     => 'boolean',
        'q35b_date_filed'             => 'date',
        'q36_convicted'               => 'boolean',
        'q37_separated_from_service'  => 'boolean',
        'q38a_election_candidate'     => 'boolean',
        'q38b_resigned_to_campaign'   => 'boolean',
        'q39_immigrant_status'        => 'boolean',
        'q40a_indigenous_group'       => 'boolean',
        'q40b_pwd'                    => 'boolean',
        'q40c_solo_parent'            => 'boolean',
        'government_id_issuance_date' => 'date',
        'date_accomplished'           => 'date',
        'attested_at'                 => 'datetime',
    ];

    /** True once every yes/no on page 4 has been answered either way. */
    public function isFullyAnswered(): bool
    {
        foreach (self::REQUIRED_ANSWERS as $field) {
            if ($this->{$field} === null) {
                return false;
            }
        }

        return true;
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_profile_id');
    }
}
