<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Services\TrackArchiveService;
use Exception;
use Illuminate\Console\Command;

class CreateTrackArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:archives';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create initial tracks archives';

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
         * @var $archiveService TrackArchiveService
         */
        $archiveService = resolve(TrackArchiveService::class);

        Track::with('archive', 'sounds')->chunk(
            50,
            function ($tracks) use ($archiveService) {
                foreach ($tracks as $track) {
                    try {
                        $archiveService->create($track);
                    } catch (Exception $e) {
                        $this->output->error($e->getMessage());
                    }
                }
            }
        );
    }
}
