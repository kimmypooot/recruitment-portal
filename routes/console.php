<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Close vacancies that have passed their 10-day publication deadline (runs daily at midnight)
Schedule::command('vacancies:close-expired')->dailyAt('00:01');

// Alert HR managers about overdue or upcoming CSC submission deadlines (runs daily at 8am)
Schedule::command('submissions:alert-overdue')->dailyAt('08:00');
