<?php

namespace App\Console;

use App\Console\Commands\SendFeedbackRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SendFeedbackRequests::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Run daily to send feedback request emails for trips ended 4 days ago
        $schedule->command('feedback:send-requests')->daily();
    }

    protected function commands()
    {
        // load commands from routes/console.php if present
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
