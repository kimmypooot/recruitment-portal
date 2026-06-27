<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdminModule();
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return $user->applicantProfile?->id === $application->applicant_id;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->canAccessAdminModule();
    }
}
