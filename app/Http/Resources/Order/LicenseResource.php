<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
{
    public function toArray($request)
    {
        self::withoutWrapping();

        return [
            'type' => $this->type
        ];
    }
}
