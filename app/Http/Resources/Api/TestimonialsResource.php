<?php

namespace App\Http\Resources\Api;

use App\Models\Structure\Testimonial;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TestimonialsResource",
 *     title="TestimonialsResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="header", type="string", example="I've been looking for a site with such a unique music, thank you very much!"),
 *     @OA\Property(property="text", type="string", example="Jack Vorobei"),
 *     @OA\Property(property="images", type="object",
 *          @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *          @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *     ),
 * )
 */
class TestimonialsResource extends JsonResource
{
    public function toArray($request)
    {
        self::withoutWrapping();

        /**
         * @var $res Testimonial
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            $this->merge($res->toArray()),
            'images' => $res->getImages()
        ];
    }
}
