<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Elastic;
use App\Interfaces\TrackInterface;

class ElasticService
{
    /**
     * @param TrackInterface $track
     * @param bool $force
     * @return string
     */
    public function mixify(TrackInterface $track, bool $force = false): string
    {
        if ($track->mix && !$force) {
            return $track->mix->text;
        }

        $mix = $this->makeMixFromTrack($track);

        Elastic::updateOrCreate([
            'track_id' => $track->id,
            'track_type' => $track->getMorphClass(),
        ], [
            'track_id' => $track->id,
            'track_type' => $track->getMorphClass(),
            'text' => $mix,
        ]);

        return $mix;
    }

    /**
     * @param TrackInterface $track
     *
     * @return string
     */
    protected function makeMixFromTrack(TrackInterface $track): string
    {
        $mix = ' ' . Str::lower($track->name);
        $mix .= ' ' . Str::lower($track->description);
        $mix .= ' ' . implode(" ", Arr::flatten($track->getSlugTags()));

        return $mix;
    }

    /**
     * @param TrackInterface $track
     *
     * @return array
     */
    public function makeSearchable(TrackInterface $track): array
    {
        $mix['name'] = Str::lower($track->name);
        $mix['description'] = Str::lower($track->description);
        $mix['tags'] = implode(',', Arr::flatten($track->getSlugTags()));

        return $mix;
    }
}
