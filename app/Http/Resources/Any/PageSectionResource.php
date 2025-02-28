<?php

namespace App\Http\Resources\Any;

use App\Models\Structure\PageSection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PageSectionResource",
 *     title="PageSectionResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="name", type="string", example="Main"),
 *     @OA\Property(property="text", type="string", example="Main")
 * )
 */
class PageSectionResource extends JsonResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        /**
         * @var $res PageSection
         */
        $res = $this->resource;
        return [
            'id' => $res->id,
            'name' => $res->name,
            'text' => $res->text,
        ];
    }
}
