<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyRecusal;

/**
 * A person who is a candidate for a vacancy must take no part in evaluating it
 * (screening, examination, interview, deliberation, decision). That includes
 * anyone who is also a board member, admin, or HR staff. The same goes for a
 * member who has declared a relationship with a candidate.
 *
 * Two things make someone conflicted: an application of their own on the
 * vacancy (withdrawn ones included: being conservative costs nothing, and a
 * withdrawal can be undone), or a `vacancy_recusals` row.
 */
class ConflictOfInterestService
{
    public function hasConflict(User $user, int $vacancyId): bool
    {
        return $this->isRecused($user->id, $vacancyId)
            || Application::where('vacancy_id', $vacancyId)
                ->whereHas('applicant', fn ($q) => $q->where('user_id', $user->id))
                ->exists();
    }

    public function isRecused(int $userId, int $vacancyId): bool
    {
        return VacancyRecusal::where('vacancy_id', $vacancyId)->where('user_id', $userId)->exists();
    }

    /** @return array<int, int> user ids recused from this vacancy */
    public function recusedUserIds(int $vacancyId): array
    {
        return VacancyRecusal::where('vacancy_id', $vacancyId)->pluck('user_id')->all();
    }

    /**
     * Records the recusal (once per vacancy and person) and audit-logs it.
     * An existing recusal is left as it was.
     */
    public function recuse(Vacancy $vacancy, User $user, string $reason, ?string $notes = null, ?User $recordedBy = null): VacancyRecusal
    {
        $recusal = VacancyRecusal::firstOrCreate(
            ['vacancy_id' => $vacancy->id, 'user_id' => $user->id],
            ['reason' => $reason, 'notes' => $notes, 'recorded_by' => $recordedBy?->id],
        );

        if ($recusal->wasRecentlyCreated) {
            AuditLog::record('vacancy_recusal_added', $recusal);
        }

        return $recusal;
    }

    /**
     * Called when someone applies: if they sit on the board for this vacancy
     * (or are admin / HR staff with evaluation access), they are recused from
     * it automatically. Plain applicants get no row; their application is
     * what blocks them from evaluating, and they have no board access anyway.
     */
    public function recuseApplicantIfStaff(Vacancy $vacancy, User $user): void
    {
        $isStaff = $user->canAccessAdminModule()
            || app(HrmpsbCompositionService::class)->isActiveMember($user, $vacancy);

        if ($isStaff) {
            $this->recuse($vacancy, $user, 'applicant');
        }
    }
}
