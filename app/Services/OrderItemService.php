<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Track;
use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrderItemService
{
    /**
     * @param Collection $tracksIds
     * @param Collection $videoEffectsIds
     * @param array $coefficients
     * @return Collection
     */
    public function getOrderItemsByIds(
        Collection $tracksIds,
        Collection $videoEffectsIds,
        array $coefficients = [],
    ): Collection
    {
        return OrderItem::where(function ($query) use ($tracksIds, $videoEffectsIds) {
            $query->where(function ($query) use ($tracksIds) {
                $query->whereIn('track_id', $tracksIds)
                    ->where('item_type', Track::class);
            })->orWhere(function ($query) use ($videoEffectsIds) {
                $query->whereIn('track_id', $videoEffectsIds)
                    ->where('item_type', VideoEffect::class);
            });
        })->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->where('price', '>', 0)
            ->with('orderItemable')->get()->map(function ($item) use ($coefficients) {
                $exclusive = $item->orderItemable->exclusive ? 0.5 : 0.4;
                $earnings = $item->price * $exclusive * (1 - $coefficients['fee']);

                $class = explode('\\', $item->item_type);

                return [
                    'date' => $item->created_at->timestamp,
                    'product_id' => $item->orderItemable->id,
                    'productName' => $item->orderItemable->name,
                    'productType' => end($class),
                    'rate' => $item->orderItemable->exclusive ? 50 : 40,
                    'discount' => 0,
                    'earnings' => (float)number_format($earnings, 2),
                    'type' => $item->license?->type,
                    'payment_type' => $item->license?->payment_type,
                    'type_licence' => $item->type_licence,
                ];
            });
    }
}
