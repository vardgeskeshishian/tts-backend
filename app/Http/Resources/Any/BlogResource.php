<?php

namespace App\Http\Resources\Any;

use App\Models\Structure\Blog;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            'image' => $res->background()->first()?->url,
            'author' => $res->author?->name,
            'body' => $res->body,
            'meta-title' => $res->metaTitle,
            'meta-description' => $res->metaDescription
        ];
    }
}
