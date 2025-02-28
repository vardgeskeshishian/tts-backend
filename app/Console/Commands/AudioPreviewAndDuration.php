<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Models\TrackAudio;
use Illuminate\Support\Str;
use App\Services\AudioService;
use Illuminate\Console\Command;

class AudioPreviewAndDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audio:preview-duration {--with-rename} {--missing-duration} {--missing-preview} {--track-id=} {--fix-preview-names}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixing audio preview and duration';

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
         * @var $service AudioService
         */
        $service = resolve(AudioService::class);

        $where = [];

        if ($this->option('missing-duration')) {
            $where['duration'] = 0;
        }

        if ($this->option('missing-preview')) {
            $where['preview_name'] = '';
        }

        if ($this->option('track-id')) {
            $where['track_id'] = $this->option('track-id');
        }

        if ($this->option('fix-preview-names')) {
            $track = Track::find($where['track_id']);

            $this->syncTrackAudiosFromFtp($track);

            return;
        }

        TrackAudio::where($where)->with('track')->chunk(1000, function ($audios) use ($service) {
            foreach ($audios as $audio) {
                if ($this->option('with-rename')) {
                    $rawUrl = storage_path("app/public/" . $audio->getRawUrlAttribute());
                    $newRawUrl = $this->getBycicleData($audio->track->id, $rawUrl);
                    $newUrl = $this->getBycicleData($audio->track->id, $audio->getOriginal('url'));

                    rename($rawUrl, $newRawUrl);
                    $audio->url = str_replace('https://static.taketones.com', '', $newUrl);

                    $this->output->text($rawUrl);
                    $this->output->text($newRawUrl);
                    $this->output->text($audio->url);
                    $this->output->text($audio->track->slug);
                }

                $preview = $service->getSongPreviewName($audio->track, $audio);
                $this->output->text($preview);

                if ($audio->duration == 0) {
                    $duration = $service->getSongDuration($audio);

                    $audio->duration = $duration;
                }

                $audio->preview_name = $preview;
                $audio->save();
            }
        });
    }

    private function syncTrackAudiosFromFtp(Track $track)
    {
        /**
         * @var $service AudioService
         */
        $service = resolve(AudioService::class);

        $audios = glob("/mnt/volume_sfo2_02/music/{$track->name}/*");

        TrackAudio::where('track_id', $track->id)->delete();

        foreach ($audios as $audioLink) {
            $audioLink = str_replace('\'', '', trim($audioLink));
            $exploded = explode('/', $audioLink);
            $splitted = array_slice($exploded, 4);

            $trackName = $splitted[0];
            [$trackFile,] = array_pad(explode('.', $splitted[1]), 2, "");
            $trackSlug = Str::slug($trackName);
            $fileExtension = pathinfo($audioLink, PATHINFO_EXTENSION);

            $trackAudioSlug = Str::slug($trackFile) . '.' . $fileExtension;

            $fileDir = '/app/public/audio/' . md5($track->id);
            $linkedDir = storage_path($fileDir);
            $fullFileDir = $linkedDir . '/' . $trackAudioSlug;

            $localFileDir = '/storage/audio/' . md5($track->id) . '/' . $trackAudioSlug;

            $trackAudio = TrackAudio::create([
                'track_id' => $track->id,
                'type' => $fileExtension,
                'preview_name' => $trackSlug,
                'duration' => $this->getSongDuration($fullFileDir),
                'url' => $localFileDir,
                'waveform' => [],
            ]);

            $trackAudio->preview_name = $service->getSongPreviewName($track, $trackAudio);
            $trackAudio->duration = $service->getSongDuration($trackAudio);
            $trackAudio->save();
        }
    }

    protected function getBycicleData($trackId, $url)
    {
        switch ($trackId) {
            case 9:
                return str_replace('disko', 'disco', $url);
            case 55:
                return str_replace('above-the-cloudsl', 'above-the-clouds', $url);
            case 63:
                return str_replace('raibow', 'rainbow', $url);
            case 98:
                return str_replace('funky-beats', 'funky-beat', $url);
            case 99:
                return str_replace('bach-air', 'bach-air-on-g-string', $url);
            case 120:
                return str_replace('groove-that-beat', 'groove-and-beat', $url);
            case 238:
                return str_replace('when-you-wake', 'when-you-wake-up', $url);
            case 407:
                return str_replace('air.waves', 'airwaves', $url);
        }
    }

    /**
     * Run ffprobe to get song duration
     *
     * @param $url
     *
     * @return float|int
     */
    protected function getSongDuration($url)
    {
        exec(
            "ffprobe -i '$url' -show_entries format=duration -v quiet -of csv=\"p=0\"",
            $output
        );

        if (count($output) > 0) {
            return (float)$output[0];
        }

        return 0;
    }
}
