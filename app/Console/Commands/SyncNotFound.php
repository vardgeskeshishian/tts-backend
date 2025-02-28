<?php

namespace App\Console\Commands;

use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncNotFound extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracks:not-found';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows not found tracks';

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
        $audios = glob('/mnt/volume_sfo2_02/music/*');

        $tracks = Track::all();

        foreach ($audios as $audioLink) {
            $audioLink = str_replace('\'', '', trim($audioLink));
            $exploded  = explode('/', $audioLink);
            $splitted  = array_slice($exploded, 4);

            $trackName = $splitted[ 0 ];
            $trackSlug = Str::slug($trackName);

            $track = $tracks->where('slug', $trackSlug)->first();

            if (! $track) {
                $this->output->error('track ' . $trackSlug . 'does not exists');
            }
        }
    }
}
