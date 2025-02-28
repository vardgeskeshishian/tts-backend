<?php

namespace App\Http\Resources\Api;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @OA\Schema(
     *     schema="BlogCategoryResource",
     *     title="BlogCategoryResource",
     *     @OA\Property(property="id", type="integer", example="12"),
     *     @OA\Property(property="slug", type="string", example="all-posts"),
     *     @OA\Property(property="name", type="string", example="All Posts"),
     *     @OA\Property(property="deletable", type="boolean", example="true"),
     *     @OA\Property(property="order", type="integer", example="1"),
     *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *     @OA\Property(property="meta", type="object",
     *          @OA\Property(property="title", type="string", example="Computer for Adobe Premiere Pro: Requirements and Specs"),
     *          @OA\Property(property="description", type="string", example="Adobe Premiere Pro is the best software for editing movies"),
     *          @OA\Property(property="keywords", type="object")
     *     ),
     * )
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $res BlogCategory
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            $this->merge($res->toArray()),
            'meta' => $res->getMeta(),
        ];
    }
}
