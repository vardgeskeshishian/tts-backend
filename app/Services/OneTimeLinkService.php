<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackAudio;
use App\Models\UserDownloads;
use Illuminate\Support\Facades\URL;

class OneTimeLinkService
{
    protected $url;
    protected $name = null;
    protected $headers = [];

    protected $types = [
        'ul', // user download license
        'ol', // order item license
        'a', // audio
        'l', // license
        'z', // zip
        'fz', // free zip
        'sz', // subscription zip,
        'item-arc' // item archive
    ];

    public function generateForAudio(TrackAudio $trackAudio)
    {
        return $this->generate('a', [
            'di' => $trackAudio->id,
        ]);
    }

    /**
     * @param string $type goes ad dt - download_type
     * @param array $fields [
     *   'dt', // type
     *   'dl', // license
     *   'dt', // track
     *   'di', // order item
     *   'do' // order
     * ];
     *
     * @param int $minutes
     *
     * @return string
     */
    public function generate(string $type, $fields = [], $minutes = 15)
    {
        $fields['dt'] = $type;

        return URL::temporarySignedRoute(
            'file.download',
            now()->addMinutes($minutes),
            $fields
        );
    }

    public function generateForUserDownloadLicense(UserDownloads $userDownloads)
    {
        return $this->generate('ul', [
            'di' => $userDownloads->id,
        ]);
    }

    public function generateFreeDownloadZip(TrackAudio $trackAudio, UserDownloads $userDownloads)
    {
        return $this->generate('fz', [
            'di' => $trackAudio->id,
            'dl' => $userDownloads->id,
        ]);
    }

    public function generateDownloadsZip(int $itemId, UserDownloads $downloads)
    {
        return $this->generate('item-arc', [
            'di' => $itemId,
            'dl' => $downloads->id,
            'item-type' => $downloads->type,
        ]);
    }

    public function generateSubDownloadZip(Track $track, UserDownloads $userDownloads)
    {
        return $this->generate('sz', [
            'di' => $track->id,
            'dl' => $userDownloads->id,
        ]);
    }
}
