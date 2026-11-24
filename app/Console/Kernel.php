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
        $schedule->command('agendamentos:executar-rodada')
            ->dailyAt('00:00')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('suporte:limpar-uploads')->daily()->withoutOverlapping();

        $schedule->command('interlab:limpar-pdfs-cartas-senha')
            ->dailyAt('03:00')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping();

        $schedule->command('interlab:retomar-lotes-postagem-orfaos')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('interlab:rastrear-itens-postagem', ['--agendado'])
            ->weekdays()
            ->at('06:00')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping();
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
