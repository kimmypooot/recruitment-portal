<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Application;
use App\Models\HrmpsbComposition;
use App\Models\User;

/**
 * Who may generate or read an applicant's CS Form No. 212.
 *
 * Applicants get their own. HRMPSB board members and the admin module get any
 * candidate's, so the board can review a PDS during screening.
 *
 * One interaction is deliberate: while an application is still masked for blind
 * deliberation, board access follows the system's existing anonymisation
 * contract rather than bypassing it — serving the PDS would hand a reviewer the
 * candidate's name, address and family details at exactly the stage the
 * AnonymizationToken exists to hide them. Users who hold the `unmask-identities`
 * gate are unaffected. Set `pds.board_access_respects_anonymization` to false to
 * grant the board unconditional access.
 */
class ApplicantProfilePolicy
{
    /** The applicant's own PDS. */
    public function generateOwn(User $user): bool
    {
        return $user->applicantProfile !== null;
    }

    /** A board member or admin reading a candidate's PDS for a given application. */
    public function viewForApplication(User $user, Application $application): bool
    {
        if (! $this->isBoardMember($user)) {
            return false;
        }

        if (! config('pds.board_access_respects_anonymization', true)) {
            return true;
        }

        $token = $application->anonymizationToken;

        // No token, or already unmasked: nothing is being hidden.
        if ($token === null || $token->isUnmasked()) {
            return true;
        }

        return $user->can('unmask-identities');
    }

    private function isBoardMember(User $user): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return HrmpsbComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }
}
