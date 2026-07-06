<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeactivateInactiveUsers extends Command
{
    protected $signature = 'account:deactivate-inactive';
    protected $description = 'Deactivate users who have been inactive for 6 months';

    public function handle(): int
    {
        $sixMonthsAgo = now()->subMonths(6);

        $count = User::whereNull('deactivated_at')
            ->where(function ($q) use ($sixMonthsAgo) {
                $q->where('last_login_at', '<', $sixMonthsAgo)
                  ->orWhere(function ($q) use ($sixMonthsAgo) {
                      $q->whereNull('last_login_at')
                        ->where('created_at', '<', $sixMonthsAgo);
                  });
            })
            ->update(['deactivated_at' => now()]);

        $this->info("Deactivated {$count} inactive user(s).");

        return self::SUCCESS;
    }
}
