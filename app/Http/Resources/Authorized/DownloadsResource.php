<?php

namespace App\Http\Resources\Authorized;

use Illuminate\Http\Resources\Json\JsonResource;

class DownloadsResource extends JsonResource
{
    public function toArray($request)
    {
        return $this->resource;
    }
}
