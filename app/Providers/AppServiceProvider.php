<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Vacancy;
use App\Policies\ApplicationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EvaluationPolicy;
use App\Policies\VacancyPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Vacancy::class, VacancyPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);

        // EvaluationPolicy gates are keyed by ability name, not model class,
        // because evaluation access depends on HRMPSB board composition.
        Gate::define('evaluate-application', [EvaluationPolicy::class, 'evaluate']);
        Gate::define('lock-evaluation', [EvaluationPolicy::class, 'lock']);
        Gate::define('unmask-identities', [EvaluationPolicy::class, 'unmask']);

        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(60, 3)->by($request->ip());
        });
    }
}
