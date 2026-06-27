<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    public function create(User $user): bool
    {
        return $user->canAccessAdminModule();
    }

    public function update(User $user, Vacancy $vacancy): bool
    {
        return $user->canAccessAdminModule();
    }

    public function publish(User $user, Vacancy $vacancy): bool
    {
        return $user->canAccessAdminModule();
    }

    public function archive(User $user, Vacancy $vacancy): bool
    {
        return $user->canAccessAdminModule();
    }

    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $user->role === 'admin';
    }
}
