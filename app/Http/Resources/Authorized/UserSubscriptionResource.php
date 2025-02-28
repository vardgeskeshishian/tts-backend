<?php

namespace App\Http\Resources\Authorized;

use App\Models\UserSubscription;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserSubscriptionResource",
 *     title="UserSubscriptionResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="license", type="string", example="Standard"),
 *     @OA\Property(property="plan", type="string", example="unknown"),
 *     @OA\Property(property="update_url", type="string", example="https://checkout.paddle.com/subscription/update?user=9507499&subscription=1529745&hash=eyJpdiI6IjhFdnZPbUwzc0k1"),
 *     @OA\Property(property="cancel_url", type="string", example="https://checkout.paddle.com/subscription/cancel?user=9507499&subscription=1529745&hash=eyJpdiI6Ik5wQzhPcFFuYTB"),
 *     @OA\Property(property="status", type="string", example="past_due"),
 *     @OA\Property(property="expiring_at", type="string", example="2023-12-20 00:00:00"),
 *     @OA\Property(property="cancelling_at", type="string", example="2023-12-20 00:00:00"),
 * )
 */
class UserSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        /**
         * @var $res UserSubscription
         */
        $res = $this->resource;

        return [
            'id'         => $res->id,
            'license'    => $res->license->type,
            'plan'       => $res->plan->plan,
            'subscription_id' => $res->subscription_id,
            'update_url' => $res->update_url,
            'cancel_url' => $res->cancel_url,
            'status'     => $res->status,
            'expiring_at' => $res->expiring_at,
            'cancelling_at' => $res->cancelling_at,
        ];
    }
}
