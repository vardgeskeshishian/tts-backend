<?php

namespace App\Http\Resources\Authorized\Collection;

use Illuminate\Http\Resources\Json\JsonResource;

class LicenseRecurrentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->type,
            'description' => $this->description,
            'price' => $this->info->price,
            'product_id' => $this->info->paddle_product_id,
            'list_1' => $this->list_1,
            'list_2' => $this->list_2
        ];
    }
}
