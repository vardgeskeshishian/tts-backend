<?php

namespace App\Http\Resources\Authorized;

use App\Models\SubscriptionHistory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SubscriptionHistoryResource",
 *     title="SubscriptionHistoryResource",
 *     @OA\Property(property="subscription", type="string", example="140391"),
 *     @OA\Property(property="payment", type="string", example="10"),
 *     @OA\Property(property="vat", type="string", example="10"),
 *     @OA\Property(property="transaction", type="string", example="Creator (monthly)"),
 *     @OA\Property(property="date", type="string", example="2019-04-27 16:44:38"),
 *     @OA\Property(property="receipt", type="string", example="http://my.paddle.com/receipt/7749526-4507982/32143105-chree48df70c2c4-1e218cabdc"),
 * )
 */
class SubscriptionHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        /**
         * @var $res SubscriptionHistory
         */
        $res = $this->resource;

        $subscription = $res->subscription;

        return [
            'subscription' => optional($subscription)->id,
            'payment'      => $res->payment,
            'vat'          => $res->vat,
            'transaction'  => $res->transaction,
            'date'         => $res->date,
            'receipt'      => $res->receipt,
        ];
    }
}
