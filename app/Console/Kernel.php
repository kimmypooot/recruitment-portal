$schedule->command('backup:run')->dailyAt('02:00');
$schedule->command('backup:monitor')->dailyAt('03:00');
$schedule->command('backup:clean')->weekly();
