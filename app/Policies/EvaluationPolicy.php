<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\HrmbsboardComposition;
use App\Models\User;

class EvaluationPolicy
{
    public function evaluate(User $user, Application $application): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return HrmbsboardComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    public function lock(User $user, Application $application): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return HrmbsboardComposition::where('user_id', $user->id)
            ->whereIn('hrmpsb_role', ['secretariat', 'hr-chief'])
            ->where('is_active', true)
            ->exists();
    }

    public function unmask(User $user): bool
    {
        return $user->canAccessAdminModule();
    }
}
