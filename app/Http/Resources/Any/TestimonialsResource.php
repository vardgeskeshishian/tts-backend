<?php

namespace App\Http\Resources\Any;

use App\Models\Structure\Testimonial;
use Illuminate\Http\Resources\Json\JsonResource;

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
            $this->merge($res->toArray()),
            'images' => $res->getImages()
        ];
    }
}
