<?php

declare(strict_types=1);

namespace App\Services\Pds;

use App\Models\ApplicantProfile;
use App\Models\PdsOtherInfo;
use Illuminate\Support\Carbon;

/**
 * Flattens an ApplicantProfile and its relations into the shape CS Form No. 212
 * (Revised 2026) expects, ready for the cell map to consume.
 *
 * Two conventions come straight from the official "Guide to Filling Up the
 * Personal Data Sheet":
 *   - "Do not leave blank entries. Put N/A if not applicable."
 *   - "Dates are in numeric format: dd/mm/yyyy."
 *
 * Both are applied here rather than in the renderer, so the PDF and Excel
 * outputs cannot drift apart.
 */
class PdsDataAssembler
{
    public const NOT_APPLICABLE = 'N/A';

    /** Row counts printed on the blank form; lists are padded to these. */
    public const PRINTED_ROWS = [
        'children'      => 12,
        'eligibilities' => 7,
        'work'          => 28,
        'voluntary'     => 7,
        'training'      => 21,
        'other_info'    => 7,
        'references'    => 3,
    ];

    public function assemble(ApplicantProfile $profile): array
    {
        // load(), not loadMissing(): a relation cached earlier in the request may
        // predate writes made since, and stale data here means a wrong form.
        $profile->load(ApplicantProfile::PDS_RELATIONS);

        return [
            'personal_information' => $this->personalInformation($profile),
            'family_background'    => $this->familyBackground($profile),
            'educational_background' => $this->educationalBackground($profile),
            'civil_service_eligibility' => $this->civilServiceEligibility($profile),
            'work_experience'      => $this->workExperience($profile),
            'voluntary_work'       => $this->voluntaryWork($profile),
            'learning_development' => $this->learningAndDevelopment($profile),
            'other_information'    => $this->otherInformation($profile),
            'declarations'         => $this->declarations($profile),
        ];
    }

    // --- Section I ----------------------------------------------------------

    private function personalInformation(ApplicantProfile $profile): array
    {
        $user = $profile->user;

        return [
            'surname'        => $this->text($user?->last_name),
            'first_name'     => $this->text($user?->first_name),
            'middle_name'    => $this->text($user?->middle_name),
            'name_extension' => $this->text($user?->suffix),

            'date_of_birth'  => $this->date($profile->birthday),
            'place_of_birth' => $this->text($profile->place_of_birth),
            'sex_at_birth'   => $this->text($profile->gender),
            'civil_status'   => $this->text($profile->civil_status),
            'civil_status_other' => $this->text($profile->civil_status_other),

            'height_m'   => $this->text($profile->height_m),
            'weight_kg'  => $this->text($profile->weight_kg),
            'blood_type' => $this->text($profile->blood_type),

            'gsis_id_no'         => $this->text($profile->gsis_id_no),
            'pagibig_id_no'      => $this->text($profile->pagibig_id_no),
            'philhealth_no'      => $this->text($profile->philhealth_no),
            'sss_no'             => $this->text($profile->sss_no),
            'tin_no'             => $this->text($profile->tin_no),
            'umid_id_no'         => $this->text($profile->umid_id_no),
            'philsys_number'     => $this->text($profile->philsys_number),
            'agency_employee_no' => $this->text($profile->agency_employee_no),

            'citizenship'              => $this->text($profile->citizenship),
            'citizenship_basis'        => $this->text($profile->citizenship_basis),
            'dual_citizenship_country' => $this->text($profile->dual_citizenship_country),

            'residential_address' => $this->address($profile, 'res_'),
            'permanent_address'   => $this->address($profile, 'perm_'),

            'telephone_no' => $this->text($profile->telephone_no),
            'mobile_no'    => $this->text($profile->mobile_number),
            'email'        => $this->text($user?->email),
        ];
    }

    private function address(ApplicantProfile $profile, string $prefix): array
    {
        return [
            'house_block_lot'   => $this->text($profile->{$prefix.'house_block_lot'}),
            'street'            => $this->text($profile->{$prefix.'street'}),
            'subdivision'       => $this->text($profile->{$prefix.'subdivision'}),
            'barangay'          => $this->text($profile->{$prefix.'barangay'}),
            'city_municipality' => $this->text($profile->{$prefix.'city_municipality'}),
            'province'          => $this->text($profile->{$prefix.'province'}),
            'zip_code'          => $this->text($profile->{$prefix.'zip_code'}),
        ];
    }

    // --- Section II ---------------------------------------------------------

    private function familyBackground(ApplicantProfile $profile): array
    {
        $family = $profile->familyBackground;

        return [
            'spouse' => [
                'surname'          => $this->text($family?->spouse_surname),
                'first_name'       => $this->text($family?->spouse_first_name),
                'middle_name'      => $this->text($family?->spouse_middle_name),
                'name_extension'   => $this->text($family?->spouse_name_extension),
                'occupation'       => $this->text($family?->spouse_occupation),
                'employer'         => $this->text($family?->spouse_employer),
                'business_address' => $this->text($family?->spouse_business_address),
                'telephone'        => $this->text($family?->spouse_telephone),
            ],
            'father' => [
                'surname'        => $this->text($family?->father_surname),
                'first_name'     => $this->text($family?->father_first_name),
                'middle_name'    => $this->text($family?->father_middle_name),
                'name_extension' => $this->text($family?->father_name_extension),
            ],
            'mother_maiden' => [
                'surname'     => $this->text($family?->mother_maiden_surname),
                'first_name'  => $this->text($family?->mother_maiden_first_name),
                'middle_name' => $this->text($family?->mother_maiden_middle_name),
            ],
            'children' => $this->pad(
                $profile->children->map(fn ($child) => [
                    'full_name'     => $this->text($child->full_name),
                    'date_of_birth' => $this->date($child->date_of_birth),
                ])->all(),
                self::PRINTED_ROWS['children'],
                ['full_name' => '', 'date_of_birth' => '']
            ),
        ];
    }

    // --- Section III --------------------------------------------------------

    /**
     * The form prints one fixed row per level, so this is keyed by level rather
     * than being a free list. Graduate studies can legitimately have several
     * entries; extras go on a continuation sheet, which is out of scope here.
     */
    private function educationalBackground(ApplicantProfile $profile): array
    {
        $byLevel = $profile->educationalAttainments->groupBy('level');

        $levels = ['elementary', 'secondary', 'vocational_trade', 'college', 'graduate_studies'];
        $rows   = [];

        foreach ($levels as $level) {
            $entry = $byLevel->get($level)?->first();

            $rows[$level] = [
                'school_name'    => $this->text($entry?->school_name),
                'degree_course'  => $this->text($entry?->degree_course),
                'period_from'    => $this->text($entry?->period_from),
                'period_to'      => $this->text($entry?->period_to),
                'units_earned'   => $this->text($entry?->units_earned),
                'year_graduated' => $this->text($entry?->year_graduated),
                'honors'         => $this->text($entry?->honors),
            ];
        }

        return $rows;
    }

    // --- Section IV ---------------------------------------------------------

    private function civilServiceEligibility(ApplicantProfile $profile): array
    {
        return $this->pad(
            $profile->civilServiceEligibilities->map(fn ($eligibility) => [
                'eligibility_name'    => $this->text($eligibility->eligibility_name),
                'rating'              => $this->text($eligibility->rating),
                'examination_date'    => $this->date($eligibility->examination_date),
                'examination_place'   => $this->text($eligibility->examination_place),
                'license_number'      => $this->text($eligibility->license_number),
                'license_valid_until' => $this->date($eligibility->license_valid_until),
            ])->all(),
            self::PRINTED_ROWS['eligibilities'],
            [
                'eligibility_name' => '', 'rating' => '', 'examination_date' => '',
                'examination_place' => '', 'license_number' => '', 'license_valid_until' => '',
            ]
        );
    }

    // --- Section V ----------------------------------------------------------

    private function workExperience(ApplicantProfile $profile): array
    {
        // "Start from your recent work."
        $sorted = $profile->workExperiences->sortByDesc(
            fn ($work) => $work->date_from?->timestamp ?? 0
        )->values();

        return $this->pad(
            $sorted->map(fn ($work) => [
                'date_from'         => $this->date($work->date_from),
                'date_to'           => $work->is_present ? 'PRESENT' : $this->date($work->date_to),
                'position_title'    => $this->text($work->position_title),
                'department_agency' => $this->text($work->department_agency),
                'monthly_salary'    => $this->money($work->monthly_salary),
                'salary_grade'      => $this->text($work->salary_grade),
                'appointment_status' => $this->text($work->appointment_status),
                'government_service' => $work->government_service ? 'Y' : 'N',
            ])->all(),
            self::PRINTED_ROWS['work'],
            [
                'date_from' => '', 'date_to' => '', 'position_title' => '',
                'department_agency' => '', 'monthly_salary' => '', 'salary_grade' => '',
                'appointment_status' => '', 'government_service' => '',
            ]
        );
    }

    // --- Section VI ---------------------------------------------------------

    private function voluntaryWork(ApplicantProfile $profile): array
    {
        return $this->pad(
            $profile->voluntaryWorks->map(fn ($work) => [
                'organization_name_address' => $this->text($work->organization_name_address),
                'date_from'                 => $this->date($work->date_from),
                'date_to'                   => $this->date($work->date_to),
                'number_of_hours'           => $this->text($work->number_of_hours),
                'position_nature_of_work'   => $this->text($work->position_nature_of_work),
            ])->all(),
            self::PRINTED_ROWS['voluntary'],
            [
                'organization_name_address' => '', 'date_from' => '', 'date_to' => '',
                'number_of_hours' => '', 'position_nature_of_work' => '',
            ]
        );
    }

    // --- Section VII --------------------------------------------------------

    private function learningAndDevelopment(ApplicantProfile $profile): array
    {
        return $this->pad(
            $profile->trainings->map(fn ($training) => [
                'title'           => $this->text($training->title),
                'date_from'       => $this->date($training->date_from),
                'date_to'         => $this->date($training->date_to),
                'number_of_hours' => $this->text($training->hours),
                'ld_type'         => $this->text($training->ld_type),
                'conducted_by'    => $this->text($training->conducted_by),
            ])->all(),
            self::PRINTED_ROWS['training'],
            [
                'title' => '', 'date_from' => '', 'date_to' => '',
                'number_of_hours' => '', 'ld_type' => '', 'conducted_by' => '',
            ]
        );
    }

    // --- Section VIII -------------------------------------------------------

    private function otherInformation(ApplicantProfile $profile): array
    {
        $grouped = $profile->otherInfos->groupBy('kind');

        $column = fn (string $kind) => $this->pad(
            ($grouped->get($kind)?->pluck('value')->map(fn ($v) => $this->text($v))->all() ?? []),
            self::PRINTED_ROWS['other_info'],
            ''
        );

        return [
            'special_skills_hobbies'   => $column(PdsOtherInfo::KIND_SPECIAL_SKILL_HOBBY),
            'non_academic_distinctions' => $column(PdsOtherInfo::KIND_NON_ACADEMIC_DISTINCTION),
            'memberships'              => $column(PdsOtherInfo::KIND_MEMBERSHIP),
        ];
    }

    // --- Page 4 -------------------------------------------------------------

    private function declarations(ApplicantProfile $profile): array
    {
        $declaration = $profile->declaration;

        return [
            'q34a' => $this->yesNo($declaration?->q34a_related_third_degree),
            'q34b' => $this->yesNo($declaration?->q34b_related_fourth_degree),
            'q34_details' => $this->text($declaration?->q34_details),

            'q35a' => $this->yesNo($declaration?->q35a_guilty_admin_offense),
            'q35a_details' => $this->text($declaration?->q35a_details),
            'q35b' => $this->yesNo($declaration?->q35b_criminally_charged),
            'q35b_date_filed'  => $this->date($declaration?->q35b_date_filed),
            'q35b_case_status' => $this->text($declaration?->q35b_case_status),

            'q36' => $this->yesNo($declaration?->q36_convicted),
            'q36_details' => $this->text($declaration?->q36_details),

            'q37' => $this->yesNo($declaration?->q37_separated_from_service),
            'q37_details' => $this->text($declaration?->q37_details),

            'q38a' => $this->yesNo($declaration?->q38a_election_candidate),
            'q38a_details' => $this->text($declaration?->q38a_details),
            'q38b' => $this->yesNo($declaration?->q38b_resigned_to_campaign),
            'q38b_details' => $this->text($declaration?->q38b_details),

            'q39' => $this->yesNo($declaration?->q39_immigrant_status),
            'q39_country' => $this->text($declaration?->q39_country),

            'q40a' => $this->yesNo($declaration?->q40a_indigenous_group),
            'q40a_group_name' => $this->text($declaration?->q40a_group_name),
            'q40b' => $this->yesNo($declaration?->q40b_pwd),
            'q40b_id_no' => $this->text($declaration?->q40b_id_no),
            'q40c' => $this->yesNo($declaration?->q40c_solo_parent),
            'q40c_id_no' => $this->text($declaration?->q40c_id_no),

            'references' => $this->pad(
                $profile->references->map(fn ($reference) => [
                    'name'                       => $this->text($reference->name),
                    'office_residential_address' => $this->text($reference->office_residential_address),
                    'contact_no_or_email'        => $this->text($reference->contact_no_or_email),
                ])->all(),
                self::PRINTED_ROWS['references'],
                ['name' => '', 'office_residential_address' => '', 'contact_no_or_email' => '']
            ),

            'government_id_type'           => $this->text($declaration?->government_id_type),
            'government_id_number'         => $this->text($declaration?->government_id_number),
            'government_id_issuance_date'  => $this->date($declaration?->government_id_issuance_date),
            'government_id_issuance_place' => $this->text($declaration?->government_id_issuance_place),
            'date_accomplished'            => $this->date($declaration?->date_accomplished),
        ];
    }

    // --- Formatting helpers -------------------------------------------------

    /** Blank becomes N/A, per the Guide. */
    private function text(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? self::NOT_APPLICABLE : $value;
    }

    /** dd/mm/yyyy, per the Guide. */
    private function date(mixed $value): string
    {
        if ($value === null || $value === '') {
            return self::NOT_APPLICABLE;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return self::NOT_APPLICABLE;
        }
    }

    /** "Indicate monthly salary in figures (e.g., P21,877.00)." */
    private function money(mixed $value): string
    {
        if ($value === null || $value === '') {
            return self::NOT_APPLICABLE;
        }

        return number_format((float) $value, 2);
    }

    /**
     * Unanswered stays null so the checkbox writer can leave BOTH boxes blank —
     * an unanswered question must not silently render as "NO".
     */
    private function yesNo(?bool $value): ?bool
    {
        return $value;
    }

    /**
     * Pad a list out to the number of rows printed on the blank form. Padding
     * rows stay empty rather than becoming N/A: the Guide's rule is about
     * unanswered *fields*, not unused rows of a repeating table.
     *
     * @param  array<int, mixed>  $rows
     * @return array<int, mixed>
     */
    private function pad(array $rows, int $count, mixed $filler): array
    {
        $rows = array_slice($rows, 0, $count);

        return array_pad($rows, $count, $filler);
    }
}
