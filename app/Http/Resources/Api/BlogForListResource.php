<?php

namespace App\Http\Resources\Api;

use App\Models\Structure\Blog;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogForListResource extends JsonResource
{
    public function toArray($request): array
    {
        /**
         * @var $res Blog
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'slug' => $res->slug,
            'title' => $res->title,
            'short_description' => $res->short_description,
            'date' => $res->created_at,
            'image' => $res->thumbnail()->first()?->url,
            'author' => $res->author?->name,
        ];
    }
}