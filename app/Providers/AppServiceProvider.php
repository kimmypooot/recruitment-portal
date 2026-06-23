<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Vacancy;
use App\Policies\ApplicationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\VacancyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Vacancy::class, VacancyPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
    }
}
