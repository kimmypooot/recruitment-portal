<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    private function isHrOrAbove(User $user): bool
    {
        return in_array($user->role, ['hr-officer', 'hr-manager', 'admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isHrOrAbove($user);
    }

    public function view(User $user, Application $application): bool
    {
        if ($this->isHrOrAbove($user)) {
            return true;
        }

        // Applicants can only view their own application
        return $user->applicantProfile?->id === $application->applicant_id;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $this->isHrOrAbove($user);
    }
}
