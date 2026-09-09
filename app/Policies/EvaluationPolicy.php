<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Application;
use App\Models\HrmpsbComposition;
use App\Models\User;

class EvaluationPolicy
{
    /**
     * NOT currently invoked anywhere (grep the repo for "evaluate-application"
     * — only the Gate::define call in AppServiceProvider references it).
     * QsEvaluationController, CbweRatingController, BeiRatingController and
     * BackgroundCheckController each re-implement this same check inline
     * instead. Left as dead code rather than wired in, because wiring it in
     * is not actually a safe drop-in replacement — see lock() below.
     */
    public function evaluate(User $user, Application $application): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return HrmpsbComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Also unused (see evaluate() above) — and its ['secretariat', 'hr-chief']
     * rule only actually matches BackgroundCheckController::isSecretariat().
     * QsEvaluationController, CbweRatingController and BeiRatingController's
     * own isSecretariat() checks allow 'secretariat' only, not 'hr-chief', so
     * wiring every controller through this method as-is would silently grant
     * hr-chief users lock power on QS/CBWE/BEI that they don't have today.
     * Whether that's a gap in those three controllers or intentional
     * (background checks being HR's own domain, unlike board evaluations)
     * wasn't verifiable from the code alone — flagging rather than guessing.
     */
    public function lock(User $user, Application $application): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return HrmpsbComposition::where('user_id', $user->id)
            ->whereIn('hrmpsb_role', ['secretariat', 'hr-chief'])
            ->where('is_active', true)
            ->exists();
    }

    public function unmask(User $user): bool
    {
        return $user->canAccessAdminModule();
    }
}
