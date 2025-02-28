<?php

namespace App\Traits;

use App\Models\Video;

trait HasVideos
{
    protected $types = [
        'background',
        'thumbnail',
        'icon',
    ];

    public function getVideos()
    {
        return $this->videos->map(function ($item) {
            return 'https://static.taketones.com' . $item->url;
        });
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'type_id')
                      ->where('type', $this->getMorphClass());
    }
}
