<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Vacancy;
use Illuminate\Support\Facades\Cache;

/**
 * See ApplicationObserver::forgetDashboardCache() — dashboard_stats also
 * counts vacancies (total/published/closing_soon), so a publish/archive/
 * status change needs to invalidate it too.
 */
class VacancyObserver
{
    public function created(Vacancy $vacancy): void
    {
        Cache::forget('dashboard_stats');
    }

    public function updated(Vacancy $vacancy): void
    {
        Cache::forget('dashboard_stats');
    }

    public function deleted(Vacancy $vacancy): void
    {
        Cache::forget('dashboard_stats');
    }
}
