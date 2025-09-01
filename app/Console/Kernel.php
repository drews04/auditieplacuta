<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DeclareDailyWinner;
use App\Services\AwardPoints;

class Kernel extends ConsoleKernel
{
    /**
     * Register Artisan commands.
     */
    protected $commands = [
        DeclareDailyWinner::class,
        \App\Console\Commands\ConcursFallbackTheme::class,
        \App\Console\Commands\ConcursDaySimulator::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
{
    // 20:00 — declare winner (Mon–Fri)
    $schedule->command('concurs:declare-winner')
             ->weekdays()
             ->dailyAt('20:00')
             ->timezone(config('app.timezone'));

    // 20:35 — award position points (Mon–Fri)
    $schedule->call(function () {
        app(\App\Services\AwardPoints::class)->awardForDate(now()->toDateString());
    })
    ->weekdays()
    ->dailyAt('20:35')
    ->timezone(config('app.timezone'));

    // 21:01 — fallback theme (if winner didn’t pick one) (Mon–Fri)
$schedule->command('concurs:fallback-theme')
->weekdays()
->timezone('Europe/Bucharest')
->at('21:01')
->withoutOverlapping();
}

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
