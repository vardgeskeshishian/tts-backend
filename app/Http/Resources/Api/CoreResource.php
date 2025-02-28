<?php

namespace App\Http\Resources\Api;

use App\Models\Structure\Core;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $res Core
         */
        $res = $this->resource;

        return [
            $this->merge($res->toArray()),
            'meta' => $res->getMeta()
        ];
    }
}
