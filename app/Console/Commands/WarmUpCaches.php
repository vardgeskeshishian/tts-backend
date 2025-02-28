<?php

namespace App\Console\Commands;

use App\Constants\CacheEnv;
use CacheServiceFacade;
use Illuminate\Console\Command;

class WarmUpCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        CacheServiceFacade::warmUpCache(CacheEnv::CACHE_LICENSES_NON_FREE_KEY);
        CacheServiceFacade::warmUpCache(CacheEnv::CACHE_USD_RATE_KEY);

        return 0;
    }
}
