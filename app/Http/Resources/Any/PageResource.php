<?php

namespace App\Http\Resources\Any;

use App\Models\Structure\Page;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PageResource",
 *     title="PageResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="url", type="string", example="main"),
 *     @OA\Property(property="title", type="string", example="Main"),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="description", type="string"),
 *     ),
 *     @OA\Property(property="sections", type="array", @OA\Items(
 *          ref="#/components/schemas/PageSectionResource"
 *     )),
 * )
 */
class PageResource extends JsonResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        /**
         * @var $res Page
         */
        $res = $this->resource;

        $sections = $res->sections;

        $result = [
            'id' => $res->id,
            'url' => $res->url,
            'title' => $res->title,
            'meta' => [
                'title' => $res->metaTitle,
                'description' => $res->metaDescription,
            ],
            'updated_at' => $res->updated_at,
        ];

        if ($sections->count() > 0)
            $result['sections'] = $sections->map(fn($item) => new PageSectionResource($item));

        return $result;
    }
}