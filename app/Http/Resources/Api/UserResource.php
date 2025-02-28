<?php

namespace App\Http\Resources\Api;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Authors\Author;
use App\Services\Finance\BalanceStatsService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @OA\Schema(
     *     schema="UserResource",
     *     title="UserResource",
     *     @OA\Property(property="id", type="string", example="140391"),
     *     @OA\Property(property="name", type="string", example="admin"),
     *     @OA\Property(property="email", type="string", example="admin@admin.com"),
     *     @OA\Property(property="two_factor_secret", type="string", example="null"),
     *     @OA\Property(property="two_factor_recovery_codes", type="string", example="null"),
     *     @OA\Property(property="avatar", type="string", example="/storage/2024/03/07/957ddf06b8d91d8524b5027481efc6ed4e8a1216.jpg"),
     *     @OA\Property(property="role", type="string", example="user"),
     *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *     @OA\Property(property="confirmed", type="string", example="false"),
     *     @OA\Property(property="downloads", type="string", example="0"),
     *     @OA\Property(property="country_code", type="string", example="US"),
     *     @OA\Property(property="user_type", type="string", example="Other Activity Type"),
     *     @OA\Property(property="previews", type="string", example="0"),
     *     @OA\Property(property="subs", type="string", example="0"),
     *     @OA\Property(property="last_preview_download", type="string", example="2024-02-27T06:20:59.000000Z"),
     *     @OA\Property(property="paypal_account", type="string", example="admin@admin.com"),
     *     @OA\Property(property="payoneer_account", type="string", example="admin@admin.com"),
     *     @OA\Property(property="country_full", type="string", example="United States"),
     *     @OA\Property(property="mailer", type="object",
    @OA\Property(property="data", type="object",
     *              @OA\Property(property="subscribed", type="string", example="false"),
     *              @OA\Property(property="status", type="string", example="unsubscribed"),
     *          )
     *     ),
     * @OA\Property(property="partner", type="object",
    @OA\Property(property="status", type="string", example="not a partner"),
     *          @OA\Property(property="links", type="array", @OA\Items(
    ref="#/components/schemas/PartnerLinks"
     *          )),
     *     ),
     * @OA\Property(property="roles", type="array", @OA\Items(
     *          ref="#/components/schemas/Role"
     *     )),
     * @OA\Property(property="author", type="object",
    @OA\Property(property="submittedTracksCount", type="string", example="4"),
     *          @OA\Property(property="profiles", type="array", @OA\Items(
    ref="#/components/schemas/Author"
     *          )),
     *     ),
     * @OA\Property(property="balance", type="object",
    @OA\Property(property="author_balance", type="string", example="4"),
     *          @OA\Property(property="partner_balance", type="string", example="4"),
     *          @OA\Property(property="total_balance", type="string", example="4"),
     *     )
     *
     * )
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $res = $this->resource;

        /**
         * @var $user User|Author
         */
        $user = $res['user'];
        $mailerStatus = $res['mailer'];
        $authorInfo = [];
        $balance = [];

        if ($user->authors()->count() > 0) {
            $balanceService = resolve(BalanceStatsService::class);
            $balance = $balanceService->setUser($user)->getCurrentBalance();

            $author = $user->getAuthor();
            $authorInfo = [
                'submittedTracksCount' => $author->submissions->count(),
                'profiles' => $author->profiles,
            ];
        }

        $subscriptions = $user->getActiveSubscriptions();

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

        return [
            $this->merge($user->toArray()),
            'mailer' => $this->merge($mailerStatus),
            'partner' => array_merge([
                'status' => 'not a partner',
                'links' => $user->partner ? $user->partner->links : [],
            ], $user->partner ? $user->partner->toArray() : []),
            'roles' => $user->roles->toArray(),
            'author' => $authorInfo,
            'balance' => $balance,
            'limits' => [
                'downloads' => [
                    'used' => $user->downloads,
                    'total' => $limits['download']
                ],
                'whitelists' => [
                    'used' => $user->whitelists,
                    'total' => $limits['whitelists']
                ],
                'claims' => [
                    'used' => $user->claims,
                    'total' => $limits['claims']
                ],
            ]
        ];
    }
}
