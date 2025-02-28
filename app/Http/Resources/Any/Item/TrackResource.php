<?php

namespace App\Http\Resources\Any\Item;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TrackResource
 * @package App\Http\Resources\Any\Item
 * @deprecated
 */
class TrackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $res Track
         */
        $res = $this->resource;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'author_name' => optional($res->author)->name ?? "",
            'description' => $this->description,
            'images' => $res->getImages(),
            'tags' => $res->getAllTags(),
            'audio' => $res->getAudioListWithFullWaveForm('wav'),
            'preview' => $res->getAudioListWithFullWaveForm('mp3'),
            'is_favorite' => $res->isFavored(),
            'prices' => $res->prices,
            'temp' => $res->tempo,
            'extra' => [
                'premium' => $res->premium,
                'has_content_id' => $res->has_content_id,
            ],
        ];
    }
}
