<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CoefficientResource",
 *     title="CoefficientResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="name", type="string", example="Exact match"),
 *     @OA\Property(property="short_name", type="string", example="ce"),
 *     @OA\Property(property="coefficient", type="integer", example="1"),
 *     @OA\Property(property="enabled", type="string", example="Enabled"),
 * )
 */
class CoefficientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'coefficient' => $this->coefficient,
            'enabled' => $this->enabled ? 'Enabled' : 'Disabled'
        ];
    }
}
