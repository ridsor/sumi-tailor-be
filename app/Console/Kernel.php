<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\DeleteOldOrder;
use App\Jobs\BackupImage;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new DeleteOldOrder)->monthly()->timezone(env('APP_TIMEZONE'));
        $schedule->job(new BackupImage)->weekly()->timezone(env('APP_TIMEZONE'));
        $schedule->command('backup:run')->weekly()->timezone(env('APP_TIMEZONE'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
