<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DeclareDailyWinner;
use App\Console\Commands\ConcursFallbackTheme;
use App\Console\Commands\ConcursDaySimulator;
use App\Console\Commands\ConcursHealthCheck;
use App\Console\Commands\ConcursInheritPoster;
use App\Services\AwardPoints;

class Kernel extends ConsoleKernel
{
    /**
     * Register Artisan commands.
     */
    protected $commands = [
        DeclareDailyWinner::class,
        ConcursFallbackTheme::class,
        ConcursDaySimulator::class,
        ConcursHealthCheck::class,
        ConcursInheritPoster::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
{
    // 20:00 — declare winner (daily, no weekend pause)
    $schedule->command('concurs:declare-winner')
        ->dailyAt('20:00')
        ->timezone(config('app.timezone', 'Europe/Bucharest'))
        ->withoutOverlapping()
        ->onOneServer();

    // 20:35 — award points
    $schedule->call(function () {
        app(\App\Services\AwardPoints::class)->awardForDate(now()->toDateString());
    })
        ->name('award-points')
        ->dailyAt('20:35')
        ->timezone(config('app.timezone', 'Europe/Bucharest'))
        ->withoutOverlapping()
        ->onOneServer();

    // 21:00 — fallback theme (daily)
    $schedule->command('concurs:fallback-theme')
        ->dailyAt('21:00')
        ->timezone(config('app.timezone', 'Europe/Bucharest'))
        ->withoutOverlapping()
        ->onOneServer();

    // 00:02 — inherit poster (daily)
    $schedule->command('concurs:inherit-poster')
        ->dailyAt('00:02')
        ->timezone(config('app.timezone', 'Europe/Bucharest'))
        ->withoutOverlapping()
        ->onOneServer();
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
