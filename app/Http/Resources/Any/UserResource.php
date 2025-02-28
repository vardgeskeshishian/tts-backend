<?php

namespace App\Http\Resources\Any;

use App\Models\Setting;
use App\Models\User;
use App\Http\Resources\Authorized\AuthorResource;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /**
         * @var $res User
         */
        $res = $this->resource;

        $subscriptions = $res->getActiveSubscriptions();

        $limits = [
            'download' => !$subscriptions['business'] && !$subscriptions['creator']
                ? Setting::where('key', 'free_downloads')->first()->value ?? 0
                : null,
            'claims' => $subscriptions['business']
                ? Setting::where('key', 'bussiness_claims')->first()->value ?? 0
                : ($subscriptions['creator'] ?
                    Setting::where('key', 'creator_claims')->first()->value ?? 0
                    : Setting::where('key', 'free_claims')->first()->value ?? 0
                ),
            'whitelists' => $subscriptions['business']
                ? Setting::where('key', 'bussiness_whitelists')->first()->value ?? 0
                : ($subscriptions['creator'] ?
                    Setting::where('key', 'creator_whitelists')->first()->value ?? 0
                    : Setting::where('key', 'free_whitelists')->first()->value ?? 0
                ),
        ];

        $result = [
            'id' => $res->id,
            'name' => $res->name,
            'email' => $res->email,
            'payout_email' => $res->payout_email,
            'subscription' => [
                'type' => $res->plan_subscriptions,
                'customerId' => $res->customer_id,
                'classic_status' => $res->subscription?->status,
                'cancel_data' => $res->subscription?->cancelling_at ?
                    Carbon::parse($res->subscription?->cancelling_at)->timestamp : null
            ],
            'roles' => $res->roles->map(function ($value) {
                return [
                    'id' => $value->id,
                    'name' => $value->name
                ];
            })->toArray(),
            'limits' => [
                'downloads' => [
                    'used' => $res->downloads,
                    'total' => $limits['download']
                ],
                'whitelists' => [
                    'used' => $res->whitelists,
                    'total' => $limits['whitelists']
                ],
                'claims' => [
                    'used' => $res->claims,
                    'total' => $limits['claims']
                ],
            ]
        ];
		
		if($request->has('meta')){
			$result['meta'] = $request->input('meta');
		}

        if (in_array(4, $res->roles->pluck('id')->toArray())) {
            $result['authors'] = $res->authors->map(fn($item) => new AuthorResource($item));
        }

        return $result;
    }
}
