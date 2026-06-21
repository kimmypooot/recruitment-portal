<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\HrmbsboardComposition;
use App\Models\User;

class EvaluationPolicy
{
    public function evaluate(User $user, Application $application): bool
    {
        if (in_array($user->role, ['admin', 'hr-manager'])) {
            return true;
        }

        return HrmbsboardComposition::where('vacancy_id', $application->vacancy_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    public function lock(User $user, Application $application): bool
    {
        if (in_array($user->role, ['admin', 'hr-manager'])) {
            return true;
        }

        return HrmbsboardComposition::where('vacancy_id', $application->vacancy_id)
            ->where('user_id', $user->id)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    public function unmask(User $user): bool
    {
        return in_array($user->role, ['admin', 'hr-manager', 'appointing-authority']);
    }
}
