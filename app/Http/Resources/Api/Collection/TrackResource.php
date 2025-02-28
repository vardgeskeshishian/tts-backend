<?php

namespace App\Http\Resources\Api\Collection;

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
            'name' => $this->name,
            'author_name' => $this->author_name,
            'hidden' => $res->hidden,
            'premium' => $res->premium,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp
        ];
    }
}
