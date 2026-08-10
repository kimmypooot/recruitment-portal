<?php

declare(strict_types=1);

namespace App\Services\Pds;

use App\Models\ApplicantProfile;
use App\Models\PdsDeclaration;
use App\Models\PdsReference;

/**
 * Reports which sections of CS Form No. 212 (Revised 2026) an applicant still
 * has to fill in. Drives the progress checklist on the profile page and gates
 * the download buttons.
 *
 * Deliberately distinct from ApplicantProfile::isComplete(), which is the much
 * looser "can this person apply at all" check. A profile can be complete for
 * application purposes and still be nowhere near a submittable PDS.
 */
class PdsCompletenessChecker
{
    /**
     * Sections the CSC will reject the form without. Education, work experience
     * and voluntary work are excluded: a fresh graduate legitimately has no work
     * history, and voluntary work is optional on the form.
     */
    public const REQUIRED_SECTIONS = [
        'personal_information',
        'family_background',
        'educational_background',
        'declarations',
        'references',
    ];

    public function check(ApplicantProfile $profile): array
    {
        // load(), not loadMissing(): a relation cached earlier in the request may
        // predate writes made since, and stale data here means a wrong form.
        $profile->load(ApplicantProfile::PDS_RELATIONS);

        $sections = [
            'personal_information'      => $this->personalInformation($profile),
            'family_background'         => $this->familyBackground($profile),
            'educational_background'    => $this->educationalBackground($profile),
            'civil_service_eligibility' => $this->civilServiceEligibility($profile),
            'work_experience'           => $this->workExperience($profile),
            'voluntary_work'            => $this->voluntaryWork($profile),
            'learning_development'      => $this->learningDevelopment($profile),
            'other_information'         => $this->otherInformation($profile),
            'declarations'              => $this->declarations($profile),
            'references'                => $this->references($profile),
        ];

        $blocking = array_values(array_filter(
            self::REQUIRED_SECTIONS,
            fn (string $key) => ! $sections[$key]['complete']
        ));

        return [
            'sections'     => $sections,
            'can_generate' => $blocking === [],
            'blocking'     => $blocking,
        ];
    }

    public function canGenerate(ApplicantProfile $profile): bool
    {
        return $this->check($profile)['can_generate'];
    }

    // --- Per-section rules --------------------------------------------------

    private function personalInformation(ApplicantProfile $profile): array
    {
        $user = $profile->user;

        $missing = [];

        $required = [
            'surname'        => $user?->last_name,
            'first_name'     => $user?->first_name,
            'date_of_birth'  => $profile->birthday,
            'place_of_birth' => $profile->place_of_birth,
            'sex_at_birth'   => $profile->gender,
            'civil_status'   => $profile->civil_status,
            'citizenship'    => $profile->citizenship,
            'mobile_no'      => $profile->mobile_number,
            'email'          => $user?->email,
        ];

        foreach ($required as $label => $value) {
            if ($this->blank($value)) {
                $missing[] = $label;
            }
        }

        // Both addresses must be answerable; the form has no "same as above" box.
        foreach (['res_' => 'residential_address', 'perm_' => 'permanent_address'] as $prefix => $label) {
            $hasAny = ! $this->blank($profile->{$prefix.'barangay'})
                || ! $this->blank($profile->{$prefix.'city_municipality'})
                || ! $this->blank($profile->{$prefix.'province'});

            if (! $hasAny) {
                $missing[] = $label;
            }
        }

        return $this->result($missing);
    }

    private function familyBackground(ApplicantProfile $profile): array
    {
        $family  = $profile->familyBackground;
        $missing = [];

        if ($family === null) {
            return $this->result(['father_name', 'mother_maiden_name']);
        }

        if ($this->blank($family->father_surname) && $this->blank($family->father_first_name)) {
            $missing[] = 'father_name';
        }

        if ($this->blank($family->mother_maiden_surname) && $this->blank($family->mother_maiden_first_name)) {
            $missing[] = 'mother_maiden_name';
        }

        return $this->result($missing);
    }

    /**
     * Elementary and secondary are the only levels every applicant must have;
     * the rest depend on how far they studied.
     */
    private function educationalBackground(ApplicantProfile $profile): array
    {
        $levels  = $profile->educationalAttainments->pluck('level')->all();
        $missing = [];

        foreach (['elementary', 'secondary'] as $level) {
            if (! in_array($level, $levels, true)) {
                $missing[] = $level;
            }
        }

        return $this->result($missing);
    }

    private function civilServiceEligibility(ApplicantProfile $profile): array
    {
        // Optional on the form, but flagged as incomplete when empty so the UI
        // can nudge — it is the single most common omission in screening.
        return $this->result(
            $profile->civilServiceEligibilities->isEmpty() ? ['no_eligibility_recorded'] : [],
            optional: true
        );
    }

    private function workExperience(ApplicantProfile $profile): array
    {
        return $this->result(
            $profile->workExperiences->isEmpty() ? ['no_work_experience_recorded'] : [],
            optional: true
        );
    }

    private function voluntaryWork(ApplicantProfile $profile): array
    {
        return $this->result(
            $profile->voluntaryWorks->isEmpty() ? ['no_voluntary_work_recorded'] : [],
            optional: true
        );
    }

    private function learningDevelopment(ApplicantProfile $profile): array
    {
        return $this->result(
            $profile->trainings->isEmpty() ? ['no_training_recorded'] : [],
            optional: true
        );
    }

    private function otherInformation(ApplicantProfile $profile): array
    {
        return $this->result(
            $profile->otherInfos->isEmpty() ? ['no_other_information_recorded'] : [],
            optional: true
        );
    }

    private function declarations(ApplicantProfile $profile): array
    {
        $declaration = $profile->declaration;

        if ($declaration === null) {
            return $this->result(PdsDeclaration::REQUIRED_ANSWERS);
        }

        $missing = array_values(array_filter(
            PdsDeclaration::REQUIRED_ANSWERS,
            fn (string $field) => $declaration->{$field} === null
        ));

        return $this->result($missing);
    }

    private function references(ApplicantProfile $profile): array
    {
        $named = $profile->references->filter(fn ($reference) => ! $this->blank($reference->name))->count();

        return $this->result(
            $named < PdsReference::REQUIRED_COUNT
                ? ['need_'.(PdsReference::REQUIRED_COUNT - $named).'_more']
                : []
        );
    }

    // --- Helpers ------------------------------------------------------------

    /**
     * `optional` sections still report what is missing so the UI can show a
     * hint, but they never block generation.
     */
    private function result(array $missing, bool $optional = false): array
    {
        return [
            'complete' => $missing === [],
            'optional' => $optional,
            'missing'  => $missing,
        ];
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}
