<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $commandsPath = __DIR__ . '/Commands';

        if (is_dir($commandsPath)) {
            $this->load($commandsPath);
        }

        $consoleRoutes = base_path('routes/console.php');

        if (file_exists($consoleRoutes)) {
            require $consoleRoutes;
        }
    }
}

