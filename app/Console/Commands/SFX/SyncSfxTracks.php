<?php

namespace App\Console\Commands\SFX;

use Exception;
use Illuminate\Console\Command;
use App\Services\TaggingService;
use App\Services\ElasticService;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Services\SFX\SoundEffectsSynchronizeService;

class SyncSfxTracks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:sfx-tracks';

    /**
     * @var ElasticService
     */
    protected $elasticService;

    /**
     * @var TaggingService
     */
    protected $taggingService;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync SFX Tracks';
    private ProgressBar $bar;
    /**
     * @var SoundEffectsSynchronizeService
     */
    private SoundEffectsSynchronizeService $service;

    /**
     * Create a new command instance.
     *
     * @param SoundEffectsSynchronizeService $service
     */
    public function __construct(SoundEffectsSynchronizeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $this->service->run();
    }
}
