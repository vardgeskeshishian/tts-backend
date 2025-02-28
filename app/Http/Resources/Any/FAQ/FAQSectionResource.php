<?php

namespace App\Http\Resources\Any\FAQ;

use App\Models\Structure\FAQSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FAQSectionResource",
 *     title="FAQSectionResource",
 *     @OA\Property(property="id", type="integer", example="140391"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="url", type="string"),
 *     @OA\Property(property="is_popular", type="string"),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="description", type="string"),
 *     ),
 *     @OA\Property(property="faqs", type="array",
 *          @OA\Items(ref="#/components/schemas/FAQResource")
 *     )
 * )
 */
class FAQSectionResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /**
         * @var $res FAQSection
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'title' => $res->title,
            'url' => $res->url,
            'is_popular' => $res->is_popular,
            'meta' => [
                'title' => $res->metaTitle,
                'description' => $res->metaDescription,
            ],
            'faqs' => $res->faqs->map(fn($faq) => new FAQResource($faq))
        ];
    }
}