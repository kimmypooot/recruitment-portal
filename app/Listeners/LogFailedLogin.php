<?php

namespace App\Listeners;

use App\Models\LoginAudit;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        try {
            LoginAudit::create([
                'user_id'    => optional($event->user)->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event'      => 'failed_login',
            ]);
        } catch (\Exception) {
        }
    }
}
