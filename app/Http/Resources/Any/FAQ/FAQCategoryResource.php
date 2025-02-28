<?php

namespace App\Http\Resources\Any\FAQ;

use App\Models\Structure\FAQCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FAQCategoryResource",
 *     title="FAQSectionResource",
 *     @OA\Property(property="id", type="integer", example="140391"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="sections", type="array",
 *          @OA\Items(ref="#/components/schemas/FAQSectionResource")
 *     )
 * )
 */
class FAQCategoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /**
         * @var $res FAQCategory
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'sections' => $res->sections->map(fn($section) => new FAQSectionResource($section))
        ];
    }
}