<?php

namespace App\Http\Resources\Order;

use App\Models\Track;
use App\Models\License;
use App\Models\SFX\SFXPack;
use App\Models\SFX\SFXTrack;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
{
    public function toArray($request)
    {
        self::withoutWrapping();

        /** @var Track|SFXTrack|SFXPack $res */
        $res = $this->resource;

        if (!$res) {
            return null;
        }

        $licenses = License::whereHas('standard', function ($q) {
            $q->where('price', '!=', 0);
        })->where('payment_type', 'standard')->get();

        $prices = [];

        foreach ($licenses as $licens) {
            $prices[$licens->id] = [
                'type' => $licens->type,
                'license_id' => $licens->id,
                'license' => $licens,
                'price' => optional($res->prices
                    ->where('license_id', $licens->id)->first())->price,
            ];
        }

        return [
            'name' => $res->name,
            'author_name' => optional($res->author)->name,
            'images' => $res->getImages(),
            'prices' => $prices,
        ];
    }
}
