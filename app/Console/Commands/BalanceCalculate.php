<?php

namespace App\Console\Commands;

use App\Jobs\BalanceCalculateJobs;
use App\Models\PayoutCoefficient;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BalanceCalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'balance calculate';

    public function handle(): void
    {
        app(BalanceService::class)->calculate();
    }
}