<?php

namespace App\Http\Resources\Any;

use App\Constants\LicenseConstants;
use App\Models\License;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
{
    public function toArray($request)
    {
        /**
         * @var $res License
         */
        $res  = $this->resource;

        $isRecurrent = $res->payment_type === LicenseConstants::RECURRENT_LICENSE;

        return [
            'id'           => $res->id,
            $this->merge($res->toArray()),
            'url'          => $res->storage_url,
            'images'       => $res->getImages(),
            'payment_type' => $res->payment_type,
            $this->mergeWhen($isRecurrent, [
                'included' => $res->included->map(function ($item) {
                    return ['id' => $item->id, 'type' => $item->type];
                }),
            ]),
        ];
    }
}
