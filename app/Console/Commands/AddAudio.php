<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Track;
use App\Models\TrackAudio;
use App\Models\TrackArchive;
use App\Services\AudioService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Jobs\RunWaveformGenerator;

class AddAudio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracks:add-audios {--not-found} {--reupload-waveform}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates symlinks from audios to tracks';

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
        $audios = glob('/mnt/volume_sfo2_02/music/*/*');

        $trackIds = [];

        $tracks = Track::with('audio')->get();
        $skipping = [];

        /**
         * @var $service AudioService
         */
        $service = resolve(AudioService::class);

        foreach ($audios as $audioLink) {
            $audioLink = str_replace('\'', '', trim($audioLink));
            $splitted = array_slice(explode('/', $audioLink), 4);

            $trackName = $splitted[0];
            [$trackFile,] = array_pad(explode('.', $splitted[1]), 2, "");
            $trackSlug = Str::slug($trackName);
            $fileExtension = pathinfo($audioLink, PATHINFO_EXTENSION);

            $trackAudioSlug = Str::slug($trackFile) . '.' . $fileExtension;

            $track = $tracks->where('slug', $trackSlug)->first();

            if (!$track && !isset($skipping[$trackSlug])) {
                $this->output->error('track ' . $trackSlug . 'does not exists');

                $skipping[$trackSlug] = true;

                continue;
            }

            if (isset($skipping[$trackSlug])) {
                continue;
            }

            if ($track->audio->count() > 0) {
                $this->output->comment("track $track->name already have {$track->audio->count()} audios");

                $skipping[$trackSlug] = true;

                continue;
            }

            $this->output->comment("track $trackSlug audio $trackAudioSlug");

            $fileDir = $this->getFileDirFromExtension($fileExtension, $track->id);

            $linkedDir = storage_path($fileDir);

            if (!is_dir($linkedDir)) {
                mkdir($linkedDir);
            }

            $localFileDir = $fileExtension === 'zip'
                ? $fileDir . '/' . $trackAudioSlug
                : '/storage/audio/' . md5($track->id) . '/' . $trackAudioSlug;

            $fullFileDir = $linkedDir . '/' . $trackAudioSlug;

            try {
                $isSymlinkCreated = symlink($audioLink, $fullFileDir);
            } catch (Exception $e) {
                if ($e->getMessage() === 'symlink(): File exists') {
                    if (!$this->option('reupload-waveform') && !in_array($track->id, $trackIds)) {
                        $this->output->text("#$track->id by name - $trackName ($trackAudioSlug)");

                        $trackIds[] = $track->id;
                    }
                }

                continue;
            }

            if (!$isSymlinkCreated) {
                $this->comment("symlink $fullFileDir already exists $trackName");

                continue;
            }

            if ($fileExtension !== 'zip') {
                $this->output->text("creating audio for $trackName as $localFileDir");

                $trackAudio = TrackAudio::create([
                    'track_id' => $track->id,
                    'type' => $fileExtension,
                    'preview_name' => $this->getSongPreviewName($trackFile),
                    'duration' => $this->getSongDuration($fullFileDir),
                    'url' => $localFileDir,
                    'waveform' => [],
                ]);

                $trackAudio->preview_name = $service->getSongPreviewName($track, $trackAudio);
                $trackAudio->duration = $service->getSongDuration($trackAudio);
                $trackAudio->save();

                if (!in_array($track->id, $trackIds)) {
                    $this->output->text("#$track->id by name - $trackName ($trackAudioSlug)");

                    $trackIds[] = $track->id;
                }

                continue;
            }

            $this->output->text("creating zip for $trackName as $localFileDir");

            TrackArchive::create([
                'track_id' => $track->id,
                'path' => $localFileDir,
            ]);
        }

        foreach ($trackIds as $trackId) {
            $this->output->text("running waveform generator for $trackId");
            RunWaveformGenerator::dispatch($trackId);
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

    /**
     * @param $name
     *
     * @return string
     */
    protected function getSongPreviewName($name)
    {
        $name = explode('.', $name)[0];
        $array = explode('-', $name);

        $preview = $array[count($array) - 1];

        return strtolower($preview);
    }

    private function getFileDirFromExtension(string $fileExtension, $trackId)
    {
        if ($fileExtension === 'zip') {
            return '/tracks/archives';
        }

        return '/app/public/audio/' . md5($trackId);
    }
}
