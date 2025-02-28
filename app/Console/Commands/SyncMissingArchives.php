<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Models\TrackArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncMissingArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:missing-archives';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing archives';

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
        Track::chunk(100, function ($tracks) {
            foreach ($tracks as $track) {
                if (!$track->archive) {
                    $zip = glob("/mnt/volume_sfo2_02/music/{$track->name}/*.zip");

                    if (count($zip) === 0) {
                        continue;
                    }

                    $zip = str_replace(' ', '\\ ', trim($zip[0]));

                    $slug = Str::slug($track->name) . '.zip';

                    $fileDir = 'tracks/archives';
                    $linkedDir = storage_path($fileDir);
                    $targetDir = $linkedDir . '/' . $slug;

                    if (! is_dir($linkedDir)) {
                        exec("mkdir -m 777 {$linkedDir} && chown root: {$linkedDir}");
                    }

                    $localFileDir = $fileDir . '/' . $slug;

                    $this->output->text("{$track->id} - {$track->name} missing archive: {$slug}, {$localFileDir}");

                    exec("ln -s -f {$zip} {$targetDir}", $o, $r);

                    TrackArchive::create([
                        'track_id' => $track->id,
                        'path'     => $localFileDir,
                    ]);
                }
            }
        });
    }
}
