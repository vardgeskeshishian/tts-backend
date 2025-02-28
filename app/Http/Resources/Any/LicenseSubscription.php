<?php

namespace App\Http\Resources\Any;

use Illuminate\Http\Resources\Json\JsonResource;

class LicenseSubscription extends JsonResource
{
    public function toArray($request)
    {
        $res = $this->resource;
        $license = $res->license;

        return [
            'plan' => $res->plan,
            'expiring_at' => $res->expiring_at,
            'list_1' => $license->list_1,
            'list_2' => $license->list_2
        ];
    }
}
