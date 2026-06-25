<?php

namespace App\Listeners;

use App\Models\LoginAudit;
use Illuminate\Auth\Events\Authenticated;

class LogAuthenticated
{
    public function handle(Authenticated $event): void
    {
        try {
            LoginAudit::create([
                'user_id'    => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event'      => 'login',
            ]);
        } catch (\Exception) {
        }
    }
}
