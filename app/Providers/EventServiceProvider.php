<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ApplicationStatusUpdated;
use App\Events\ApplicationSubmitted;
use App\Events\QSEvaluationsLocked;
use App\Events\VacancyStateChanged;
use App\Listeners\LogApplicationStatusUpdated;
use App\Listeners\LogApplicationSubmitted;
use App\Listeners\LogAuthenticated;
use App\Listeners\LogFailedLogin;
use App\Listeners\LogQSEvaluationsLocked;
use App\Listeners\LogVacancyStateChanged;
use App\Listeners\SendApplicationStatusUpdatedNotification;
use App\Listeners\SendApplicationSubmittedNotification;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Authenticated::class => [
            LogAuthenticated::class,
        ],
        Failed::class => [
            LogFailedLogin::class,
        ],

        // Application lifecycle
        ApplicationSubmitted::class => [
            LogApplicationSubmitted::class,
            SendApplicationSubmittedNotification::class,
        ],
        ApplicationStatusUpdated::class => [
            LogApplicationStatusUpdated::class,
            SendApplicationStatusUpdatedNotification::class,
        ],

        // Vacancy lifecycle
        VacancyStateChanged::class => [
            LogVacancyStateChanged::class,
        ],

        // Pipeline stage events
        QSEvaluationsLocked::class => [
            LogQSEvaluationsLocked::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
