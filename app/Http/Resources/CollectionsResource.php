<?php


namespace App\Http\Resources;

use App\Models\Structure\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionsResource extends JsonResource
{
    public function toArray($request)
    {
        /**
         * @var $res Collection
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'description' => $res->description,
            'price' => $res->price,
            'url' => $res->url,
            'meta' => $res->getMeta(),
            'images' => $res->getImages(),
            'tags' => $res->getAllTags(),
            'hidden' => $res->hidden,
        ];
    }
}
