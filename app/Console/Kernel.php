<?php

namespace App\Console;

use App\Console\Commands\BalanceCalculate;
use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\MusicRecovery;
use App\Console\Commands\MusicRecoveryBetta;
use App\Console\Commands\MusicRecoveryGlob;
use App\Console\Commands\ResetCountersDownloads;
use App\Console\Commands\UpdateCoefficient;
use App\Console\Commands\SyncUserSubscriptionClassic;
use App\Console\Commands\WebhookClear;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BalanceCalculate::class,
        ResetCountersDownloads::class,
		GenerateSitemap::class,
		MusicRecovery::class,
		MusicRecoveryBetta::class,
		MusicRecoveryGlob::class,
        UpdateCoefficient::class,
        SyncUserSubscriptionClassic::class,
        WebhookClear::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('balance:calculate')->monthlyOn(1, '01:00');
        $schedule->command('counters:reset')->monthly();
		$schedule->command('sitemap:generate')->daily();
        $schedule->command('update:coefficients')->daily();
        $schedule->command('telescope:prune --hours=48')->daily();
        $schedule->command('webhook:clear')->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
