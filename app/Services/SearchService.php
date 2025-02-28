<?php

namespace App\Services;

use App\Models\Track;
use App\Models\Tags\Genre;
use App\Models\Tags\Tagging;
use App\Services\SearchStrategies\AbstractSearch;
use App\Http\Resources\Any\Collection\TrackResource;

class SearchService
{
    public function similar(Track $track)
    {
        $genre = $track->getTagsOfType('genres');

        if (count($genre) > 0) {
            $genre = Genre::where('slug', $genre[0]['slug'])->first();
        } else {
            return [];
        }

        $similarTracksId = Tagging::where([
            'tag_type' => Genre::class,
            'tag_id' => $genre->id,
        ])->where('object_id', '!=', $track->id)->get()->pluck('object_id')->all();

        $relevancyTags = collect($track->getTagsOfType('tags'))->map(function ($item) {
            return $item['slug'];
        });
        $similarTracks = Track::with('audio', 'images', 'prices', 'author')
            ->whereIn('id', $similarTracksId)
            ->where('id', '!=', $track->id)
            ->get();

        foreach ($similarTracks as $similarTrack) {
            /**
             * @var $similarTrack Track
             */
            $tags = collect($similarTrack->getTagsOfType('tags'))->map(function ($item) {
                return $item['slug'];
            });

            $similarTrack->relevancy = $relevancyTags->intersect($tags)->count();
        }

        $tracks = $similarTracks
            ->where('relevancy', '>', 0)
            ->where('has_content_id', false)
            ->sortByDesc('created_at')
            ->sortByDesc('relevancy')
            ->take(3);

        return TrackResource::collection($tracks);
    }

    private function setStrategy(string $class): AbstractSearch
    {
        return resolve($class);
    }
}
