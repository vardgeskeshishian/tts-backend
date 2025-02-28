<?php

namespace App\Helpers;

use App\Constants\Env;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Support\Collection;

class AuthorItems
{
    public $count = 0;
    public Collection $tracks;
    public Collection $templates;
    public array $trackIds = [];
    public array $templateIds = [];
    public Collection $items;
    public array $itemIds = [];

    /**
     * @param Collection $profileIds
     * @return AuthorItems
     */
    public function getAuthorItems(Collection $profileIds)
    {
        $tracks = Track::when(count($profileIds) > 0, fn($q) => $q
            ->whereIn('author_profile_id', $profileIds))->get();
        $templates = VideoEffect::when(count($profileIds) > 0, fn($q) => $q
            ->whereIn('author_profile_id', $profileIds))->get();

        $this->tracks = $tracks;
        $this->templates = $templates;
        $this->trackIds = $tracks->pluck('id')->all();
        $this->templateIds = $templates->pluck('id')->all();
        $this->itemIds = $tracks->pluck('id')->merge($templates->pluck('id'))->all();
        $this->items = $tracks->merge($templates);
        $this->count = $this->items->count();

        return $this;
    }
}