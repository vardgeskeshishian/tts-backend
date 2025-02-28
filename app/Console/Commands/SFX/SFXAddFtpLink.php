<?php

namespace App\Console\Commands\SFX;

use Carbon\Carbon;
use App\Models\SFX\SFXTrack;
use Illuminate\Console\Command;

class SFXAddFtpLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfx:sync-ftp-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SFX: Sync FTP Links for new syncronisation system';

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
     * @return void
     */
    public function handle()
    {
        $effects = glob('/mnt/volume_sfo2_02/SFX/*.wav');

        foreach ($effects as $effect) {
            $filename = pathinfo($effect, PATHINFO_FILENAME);
            $extension = pathinfo($effect, PATHINFO_EXTENSION);

            SFXTrack::where([
                'name' => $filename,
                'extension' => $extension,
            ])->update([
                'synced_at' => Carbon::createFromTimestamp(filemtime($effect)),
                'ftp_link' => $effect,
            ]);
        }
    }
}
