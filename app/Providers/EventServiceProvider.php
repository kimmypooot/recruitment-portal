<?php

namespace App\Providers;

use App\Listeners\LogAuthenticated;
use App\Listeners\LogFailedLogin;
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
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
