<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TmpFixBalancesConfirmedAtTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:fix-balances-updated-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes confirmed_at time of balances';

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

        return 0;
    }
}
