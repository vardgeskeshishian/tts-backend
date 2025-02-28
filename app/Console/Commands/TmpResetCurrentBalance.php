<?php

namespace App\Console\Commands;

use App\Models\Finance\Balance;
use App\Services\Finance\FinanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TmpResetCurrentBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:reset-current-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets current author balance to only contain sales awards';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Balance::where('date', FinanceService::getFinanceDate(Carbon::now()))->each(function (Balance $balance) {
            if ($balance->date !== '2021-05') {
                return false;
            }

            $balance->author_balance = $balance->details->sum('award');
            $balance->save();

            dump($balance->author_balance);
            return true;
        });

        Balance::whereNull('payment_email')->each(function (Balance $balance) {
            $prevBalance = Balance::where('user_id', $balance->user_id)
                ->whereNotNull('payment_email')
                ->first();

            if (!$prevBalance) {
                return true;
            }

            $balance->payment_email = $prevBalance->payment_email;
            $balance->payment_type = $prevBalance->payment_type;

            $balance->save();
            dump($balance->payment_email);
            return true;
        });

        return 0;
    }
}
