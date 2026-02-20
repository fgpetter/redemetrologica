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
        // $schedule->command('app:envia-convites')->everyTenSeconds();
        // $schedule->command('certificados:clean')->dailyAt('01:00');
        $schedule->command('app:send-envio-amostras-notification')->dailyAt('00:00');
        $schedule->command('app:send-inicio-ensaios-notification')->dailyAt('01:00');
        $schedule->command('app:send-limite-envio-resultados-notification')->dailyAt('02:00');
        $schedule->command('app:send-divulgacao-relatorios-notification')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
