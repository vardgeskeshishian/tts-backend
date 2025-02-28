<?php

namespace App\Http\Resources\Authorized;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            $this->merge($res->toArray()),
            'images' => $res->getImages(),
            'tags' => $res->getAllTags(),
            'audio' => $res->audio,
            'prices' => $res->prices
        ];
    }
}
