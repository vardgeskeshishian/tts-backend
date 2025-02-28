<?php

namespace App\Console\Commands;

use App\Jobs\ConvertWavToMp3 as ConvertWavToMp3Alias;
use App\Models\Track;
use Illuminate\Console\Command;

class ConvertWavToMp3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:wav-mp3 {--track-id=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts wav to mp3';

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
        if ($this->option('track-id')) {
            $tracks = [$this->option('track-id')];
        } else {
            $tracks = Track::select('id')->pluck('id')->toArray();
        }

        foreach ($tracks as $trackId) {
            $this->output->comment("Starting to convert {$trackId}");
            ConvertWavToMp3Alias::dispatch($trackId, $this->option('force'));
        }
    }
}
