<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    private function isHrOrAbove(User $user): bool
    {
        return in_array($user->role, ['hr-officer', 'hr-manager', 'admin']);
    }

    private function isHrManagerOrAbove(User $user): bool
    {
        return in_array($user->role, ['hr-manager', 'admin']);
    }

    public function create(User $user): bool
    {
        return $this->isHrOrAbove($user);
    }

    public function update(User $user, Vacancy $vacancy): bool
    {
        return $this->isHrOrAbove($user);
    }

    public function publish(User $user, Vacancy $vacancy): bool
    {
        return $this->isHrOrAbove($user);
    }

    public function archive(User $user, Vacancy $vacancy): bool
    {
        return $this->isHrOrAbove($user);
    }

    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $this->isHrManagerOrAbove($user);
    }
}
