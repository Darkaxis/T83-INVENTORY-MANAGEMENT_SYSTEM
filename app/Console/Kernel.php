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
        // Check for updates daily at midnight
        $schedule->command('app:check-for-updates')->daily();
        
        // Auto-deploy updates to rings with auto_update enabled at 2 AM
        $schedule->command('deploy:pending-updates')->dailyAt('2:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}