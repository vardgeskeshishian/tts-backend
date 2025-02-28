<?php

namespace App\Console\Commands;

use App\Models\UserDownloads;
use App\Services\Finance\BalanceService;
use Illuminate\Console\Command;

class RestoryStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:restore-missing-statements {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore missing statements';
    private BalanceService $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BalanceService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $downloads = UserDownloads::where('type', 'video_effects')->get();

        $downloads->each(function (UserDownloads $download) {
            $userId = $download->track_id;
            $this->service->award();
        });
    }
}
