<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register scheduled tasks here (Kernel delegates scheduling to this file)
app()->booted(function () {
    $schedule = app(Schedule::class);

    // Send feedback request emails on the 4th day after trip completion at 10:00 daily
    $schedule->command('feedback:send-requests')->dailyAt('10:00');

    // Update trip statuses based on booking dates at 12:01 AM daily
    $schedule->command('trips:update-statuses')->dailyAt('00:01');
});
