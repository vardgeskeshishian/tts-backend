<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrackAudio;

class TmpFixTrackAudioUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:fix-track-audio-urls';

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

        TrackAudio::where('track_id', 641)->each(function ($audio) {
            $md5 = md5(641);
            $name = $audio->preview_name;
            dump($audio->getRawOriginal('type'));
            $audioEnding = $audio->getRawOriginal('type') === 'mp3' ? "preview-{$name}.mp3" : "{$name}.wav";
            $audio->url = "/storage/audio/{$md5}/{$audio->track->slug}-{$audioEnding}";
            $audio->save();


        });
        return 0;
    }
}
