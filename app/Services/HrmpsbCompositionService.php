<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HrmpsbComposition;
use App\Models\PlaceOfAssignmentHead;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Collection;

class HrmpsbCompositionService
{
    public const HRD_PLACE = 'Human Resource Division (HRD)';

    public const ORD_PLACE = 'Office of the Regional Director (ORD)';

    /**
     * Places of assignment whose Head of Unit is excluded from the dynamic
     * HRMPSB composition:
     *   - HRD: the Chief of HR Division is already a mandatory HRMPSB member.
     *   - ORD: the head of the office is the Appointing Authority, who plays
     *     a separate role in the recruitment process (final appointment
     *     decision) rather than as a Head of Unit board member — so a
     *     Head of Unit designation does not apply here.
     */
    public const HEAD_OF_UNIT_EXCLUDED_PLACES = [
        self::HRD_PLACE,
        self::ORD_PLACE,
    ];

    /**
     * Get the effective HRMPSB composition for a specific vacancy.
     * Merges global (static) compositions with the dynamically determined
     * Head of Unit based on the vacancy's place_of_assignment.
     *
     * For HRD, Head of Unit is excluded because the Chief of HR Division is
     * already a mandatory HRMPSB member. For ORD, it's excluded because the
     * head of that office is the Appointing Authority, for whom a Head of
     * Unit designation does not apply.
     */
    public function forVacancy(Vacancy $vacancy): Collection
    {
        $static = HrmpsbComposition::with('user:id,first_name,last_name,middle_name,suffix,email,role')
            ->where('is_active', true)
            ->orderBy('hrmpsb_role')
            ->get();

        if ($this->isHeadOfUnitExcluded($vacancy)) {
            return $static;
        }

        $head = $this->resolveHeadOfUnit($vacancy->place_of_assignment);

        if ($head === null) {
            return $static;
        }

        $dynamic = (object) [
            'user_id' => $head->id,
            'hrmpsb_role' => 'head-of-unit',
            'member_type' => 'principal',
            'is_active' => true,
            'user' => $head,
            'is_dynamic' => true,
        ];

        return $static->push($dynamic)->sortBy('hrmpsb_role')->values();
    }

    public function isHrdVacancy(Vacancy $vacancy): bool
    {
        return $vacancy->place_of_assignment === self::HRD_PLACE;
    }

    public function isOrdVacancy(Vacancy $vacancy): bool
    {
        return $vacancy->place_of_assignment === self::ORD_PLACE;
    }

    public function isHeadOfUnitExcluded(Vacancy $vacancy): bool
    {
        return in_array($vacancy->place_of_assignment, self::HEAD_OF_UNIT_EXCLUDED_PLACES, true);
    }

    public function resolveHeadOfUnit(string $placeOfAssignment): ?User
    {
        $mapping = PlaceOfAssignmentHead::with('user')
            ->where('place_of_assignment', $placeOfAssignment)
            ->first();

        return $mapping?->user;
    }
}
