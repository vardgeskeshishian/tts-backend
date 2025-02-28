<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Services\ElasticService;
use Illuminate\Console\Command;

class UpdateElastic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:elastic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tracks mix';
    protected $elastic;

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
     * @return mixed
     */
    public function handle()
    {
        /**
         * @var $elasticService ElasticService
         */
        $elasticService = resolve(ElasticService::class);

	Track::with('mix')->each(function (Track $track) use ($elasticService) {
            $elasticService->mixify($track, true);
        }, 50);
    }
}
