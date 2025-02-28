<?php

namespace App\Services;

use App\Models\SFX\SFXTrack;
use Illuminate\Support\Facades\Storage;

class SFXTrackService
{
    /**
     * Run ffprobe to get song duration
     *
     * @param SFXTrack $track
     * @return float|int
     */
    public function getSongDuration(SFXTrack $track): float|int
    {
        $fullStorageLink = base_path().$track->link;
        exec(
            "ffprobe -i $fullStorageLink -show_entries format=duration -v quiet -of csv=\"p=0\"",
            $output
        );

        if (count($output) > 0) {
            return floatval($output[0]);
        }

        return 0;
    }

    /**
     * @param SFXTrack $track
     * @return string
     */
    public function getSongWaveform(SFXTrack $track): string
    {
        $pathJson = '/sfx';
        if (!Storage::exists($pathJson))
            Storage::createDirectory($pathJson);

        $pathJson = Storage::path($pathJson).'/'.$track->id.'.json';
        exec('audiowaveform -i '.base_path().$track->link.' -o '.$pathJson.' -b 8 -z 12000', $output);

        $json = file_get_contents($pathJson);
        $json = json_decode($json, true);
        return json_encode($json['data']);
    }
}