<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="MainPageResource",
 *     title="MainPageResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="section_id", type="string", example="section_5"),
 *     @OA\Property(property="type", type="string", example="description"),
 *     @OA\Property(property="text", type="string", example="Save Time and Effort with Our Professional Editable Video Templates"),
 *     @OA\Property(property="page_type", type="string", example="vfx"),
 * )
 */
class MainPageResource extends JsonResource
{
    public function toArray($request)
    {
        $res = $this->resource;
        
        return [
            'id' => $res->id,
            $this->merge($res->toArray()),
        ];
    }
}
