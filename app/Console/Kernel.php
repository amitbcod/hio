<?php

namespace App\Console;

use App\Console\Commands\SendFeedbackRequests;
use App\Console\Commands\UpdateTripStatuses;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SendFeedbackRequests::class,
        UpdateTripStatuses::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Scheduling is registered in routes/console.php for this project.
        // Keep this method present for compatibility; tasks are added in routes/console.php.
    }

    protected function commands()
    {
        // load commands from routes/console.php if present
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
