<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Models\TrackAudio;
use Exception;
use Illuminate\Support\Str;
use App\Models\TrackArchive;
use Illuminate\Console\Command;

class RenameTrack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:rename {--track-id=} {--rename-from=} {--rename-to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renames track, move symlinks, updates zip';

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
        $track = Track::find($this->option('track-id'));
        $renameFrom = $this->hasOption('rename-from')
            ? $this->option('rename-from')
            : $track->name;
        $renameTo = $this->option('rename-to');

        if (!$renameTo) {
            return;
        }

        $dir = "/mnt/volume_sfo2_02/music";

        try {
            rename("{$dir}/{$renameFrom}", "{$dir}/{$renameTo}");
        } catch (Exception $exception) {
            $this
                ->output
                ->text("directory already renamed from {$renameFrom} to {$renameTo}");
        }

        $audios = glob("/mnt/volume_sfo2_02/music/{$renameTo}/*");
        
        foreach ($audios as $audio) {
            $oldPath = $audio;
            $newPath = str_replace($renameFrom, $renameTo, $oldPath);

            try {
                rename($oldPath, $newPath);
            } catch (Exception $exception) {
                $this
                    ->output
                    ->text("file already renamed from {$oldPath} to {$newPath}");
            }

            $fileExtension = pathinfo($newPath, PATHINFO_EXTENSION);

            if ($fileExtension !== 'zip') {
                $fileDir = 'app/public/audio/' . md5($track->id);
            } else {
                $fileDir = 'tracks/archives';
            }

            $fileExtension = pathinfo($audio, PATHINFO_EXTENSION);

            $exploded = explode('/', $audio);
            $splitted = array_slice($exploded, 4);

            [$trackFile, ]  = array_pad(explode('.', $splitted[ 1 ]), 2, "");

            $trackAudioSlug = Str::slug($trackFile) . '.' . $fileExtension;

            $linkedDir = storage_path($fileDir);

            $localFileDir = $fileExtension === 'zip'
                ? $fileDir . '/' . $trackAudioSlug
                : '/storage/audio/' . md5($track->id) . '/' . $trackAudioSlug;

            $fullFileDir = $linkedDir . '/' . $trackAudioSlug;

            try {
                symlink($audio, $fullFileDir);
            } catch (Exception $exception) {
                $this->output->text("symlink {$audio} to {$fullFileDir} already exists");
            }

            if ($fileExtension !== 'zip') {
                TrackAudio::where([
                    'track_id'     => $track->id,
                    'type'         => $fileExtension,
                    'preview_name' => $this->getSongPreviewName($renameTo, $fullFileDir)
                ])->update([
                    'url'          => $localFileDir,
                ]);
            } else {
                TrackArchive::where([
                    'track_id' => $track->id,
                ])->update([
                    'path'     => $localFileDir,
                ]);
            }
        }

        $track->name = $renameTo;
        $track->slug = Str::slug($renameTo);
        $track->save();
    }

    /**
     * @param $oldName
     * @param $audioUrl
     *
     * @return string
     */
    protected function getSongPreviewName($oldName, $audioUrl)
    {
        $slug = Str::slug($oldName);

        $exploded = explode('/', $audioUrl);
        $audioName = $exploded[count($exploded) - 1];
        $previewFileName = ltrim(str_replace([$slug, 'preview'], '', $audioName), '-');
        $preview = explode('.', $previewFileName)[0];

        $preview = str_replace('-', ' ', $preview);
        $preview = preg_replace('/(loop)\s(\d)/', '$1$2', $preview);

        return strtolower($preview);
    }
}
