<?php

namespace App\Http\Resources\Any\FAQ;

use App\Models\Structure\FAQ;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FAQResource",
 *     title="FAQResource",
 *     @OA\Property(property="id", type="integer", example="140391"),
 *     @OA\Property(property="question", type="string"),
 *     @OA\Property(property="answer", type="string"),
 * )
 */
class FAQResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request)
    {
        /**
         * @var $res FAQ
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'question' => $res->question,
            'answer' => $res->answer
        ];
    }
}