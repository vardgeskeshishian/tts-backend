<?php

namespace App\Console\Commands;

use App\Jobs\RunWaveformGenerator;
use Illuminate\Console\Command;

class GenerateMissingAudioWaveforms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracks:missing-audio {--track-ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing audio waveforms';

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
        $trackIds = explode(',', $this->option('track-ids'));

        foreach ($trackIds as $trackId) {
            $this->output->text("running waveform generator for {$trackId}");

            RunWaveformGenerator::dispatch($trackId);
        }
    }
}
