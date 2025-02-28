<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Jobs\CancelSubscription;
use App\Models\User;
use App\Models\Order;
use App\Constants\Env;
use App\Models\OrderItem;
use App\Models\TrackAudio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use App\Models\UserDownloads;
use App\Services\UserService;
use App\Services\OrderService;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use App\Models\SubscriptionHistory;
use App\Services\MailerLite\MailerLiteService;
use App\Services\OneTimeLinkService;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterConfirmationMail;
use App\Http\Resources\Any\UserResource;
use App\Http\Controllers\Api\AuthorizedController;
use App\Http\Resources\Authorized\DownloadsResource;
use App\Http\Resources\Authorized\UserSubscriptionResource;
use App\Http\Resources\Authorized\SubscriptionHistoryResource;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use App\Http\Requests\UserRequest;

class UserController extends AuthorizedController
{
    /**
     * @var MailerLiteService
     */
    private $mailerLiteService;
    /**
     * @var OneTimeLinkService
     */
    private $oneTimeLinkService;

    public function __construct(
        OneTimeLinkService $oneTimeLinkService,
        MailerLiteService $mailerLiteService
    ) {
        $this->oneTimeLinkService = $oneTimeLinkService;
        $this->mailerLiteService = $mailerLiteService;
    }

    /**
     * @OA\Get(
     *     path="/v1/protected/me",
     *     summary="Authorized user information",
     *     tags={"Me"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserResource")),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = auth()->user();
		$request->merge(['meta' => [
			'hash_user_id' => hash_hmac(
				algo : 'sha256',
				data: $user->id,
				key : 'userauthkey-7304-e4b570840102bd5bba44c9d28b9a9e8dd1788216d056a070ee75f68adb06ab5'
			),
		]]);
        return response()->json(new UserResource($user));
    }

    /**
     * Return User Settings
     *
     * @OA\Post(
     *     path = "/v1/protected/me/update",
     *     summary = "Return User Settings",
     *     tags={"Me"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="name", type="string", description="Name User"),
     *          @OA\Property(property="email", type="string", description="Email User"),
     *          @OA\Property(property="payout_email", type="string", description="Payout Email User"),
     *          @OA\Property(property="plan_subscriptions", type="string", description="Plan Subscription: classic or billing"),
     *          @OA\Property(property="customer_id", type="string", description="Customer Id")
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="string", example="140391"),
     *                   @OA\Property(property="name", type="string", example="admin"),
     *                   @OA\Property(property="email", type="string", example="admin@admin.com"),
     *                   @OA\Property(property="plan_subscriptions", type="string"),
     *                   @OA\Property(property="customer_id", type="string"),
     *                   @OA\Property(property="roles", type="array", @OA\Items(
     *                      ref="#/components/schemas/Role"
     *                   )),
     *                   @OA\Property(property="author", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="integer", example="0"),
     *                      @OA\Property(property="name", type="string"),
     *                   )),
     *              )
     *         )
     *     ),
     * )
     *
     */
    public function update(UserRequest $request): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = auth()->user();
        $user->update($request->toArray());

        return response()->json(new UserResource($user));
    }

    /**
     * Return User Orders
     *
     * @OA\Get(
     *     path="/v1/protected/me/orders",
     *     summary="Return User Orders",
     *     tags={"Me"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/OrdersList"
     *         ),
     *     ),
     * )
     *
     */
    public function orders()
    {
        return resolve(UserService::class)->ordersList();
    }

    /**
     *
     * @OA\Post(
     *     path = "/v1/protected/me/orders/download",
     *     summary = "Orders Download",
     *     tags={"Me"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="file_type", type="string", description="File Type: license, track, archive"),
     *          @OA\Property(property="type", type="string", description="Type: free, subscription, order"),
     *          @OA\Property(property="sourceId", type="string", description="User Download ID Or Order Item ID"),
     *          @OA\Property(property="with_license", type="boolean", description="With license"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="link", type="string")
     *              )
     *         )
     *     ),
     * )
     *
     * @return JsonResponse|void
     */
    public function ordersDownload()
    {
        $fileType = request('file_type'); // license, track, archive
        $downloadType = request('type'); // free, subscription, order
        $sourceId = request('downloads_id');
        $withLicense = request('with_license', true);

        $isDownloads = in_array($downloadType, ['free', 'subscription']);
        $source = $isDownloads
            ? UserDownloads::find($sourceId)
            : OrderItem::find($sourceId);

        if ($fileType === 'license' && $isDownloads) {
            return $this->success([
                'link' => $this
                    ->oneTimeLinkService
                    ->generateForUserDownloadLicense($source),
            ]);
        }

        if ($fileType === 'license') {
            return $this->success([
                'link' => $this
                    ->oneTimeLinkService
                    ->generate('ol', [
                        'di' => $sourceId,
                        'item-type' => $source->item_type,
                    ]),
            ]);
        }

        if ($fileType === 'track') {
            $track = $source->track;
            $audios = $track->audio;
            $fullId = null;

            /**
             * @var $audio TrackAudio
             */
            foreach ($audios as $audio) {
                if ($audio->format === "wav" && strtolower($audio->preview_name) === 'full') {
                    $fullId = $audio->id;
                }
            }

            return $this->success([
                'link' => $this
                    ->oneTimeLinkService
                    ->generate('a', [
                        'di' => $fullId,
                    ]),
            ]);
        }

        if ($fileType === 'archive') {
            return $this->success([
                'link' => $this
                    ->oneTimeLinkService
                    ->generate('item-arc', [
                        'di' => $isDownloads ? $source->track_id : ($source->item_id ?? $source->track_id),
                        'dl' => $source->id,
                        'item-type' => $isDownloads
                            ? in_array($source->type, [Env::ITEM_TYPE_EFFECTS, Env::ITEM_TYPE_PACKS])
                                ? $source->type
                                : Env::ITEM_TYPE_TRACKS
                            : $source->item_type,
                        'd-type' => $isDownloads ? 'downloads' : 'orders',
                        'wl' => $withLicense,
                    ]),
            ]);
        }
    }

    /**
     * List of tracks (bought or downloaded)
     *
     * @OA\Get(
     *     path="/v1/protected/me/downloads",
     *     summary="List of tracks (bought or downloaded)",
     *     tags={"Me"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
                        @OA\Property(property="order_id", type="string", example="4"),
     *                  @OA\Property(property="date", type="string", example="1602418511"),
     *                  @OA\Property(property="license_number", type="string", example="TTStandard17418547"),
     *                  @OA\Property(property="type", type="string", example="Free"),
     *                  @OA\Property(property="track", type="object",
     *                      @OA\Property(property="name", type="string", example="Endless Inspiration"),
     *                      @OA\Property(property="author", type="string", example="Alex Stoner"),
     *                  )
     *              )),
     *         ),
     *     ),
     * )
     *
     * @return DownloadsResource
     */
    public function downloads(): DownloadsResource
    {
        $orders = Order::where([
            'user_id' => auth()->id(),
            'status' => Env::STATUS_FINISHED,
        ])->get();

        $items = [];

        foreach ($orders as $order) {
            /**
             * @var $item OrderItem
             */
            foreach ($order->items as $item) {
                $items[] = [
                    'order_id' => $order->id,
                    'date' => $item->updated_at->timestamp,
                    'license_number' => $item->license_number,
                    'type' => $item->license->type,
                    'track' => [
                        'name' => $item->track->name,
                        'author' => optional($item->track->author)->name,
                    ],
                ];
            }
        }

        return new DownloadsResource($items);
    }

    /**
     * Return User Subscription Plan
     */
    public function subscriptionPlan()
    {
        return $this->success();
    }

    /**
     * Find latest order and show it as cart
     *
     * @return JsonResponse
     */
    public function cart(): JsonResponse
    {
        return $this->wrapCall(OrderService::class, 'cart');
    }

    /**
     * Returns data for mini-cart (amount, sum)
     *
     * @return JsonResponse
     */
    public function miniCart()
    {
        $res = $this->wrapCall(OrderService::class, 'cart');

        return $this->success([
            'count' => isset($res['items']) ? count($res['items']) : 0,
            'sum' => $res['sum'] ?? 0,
        ]);
    }

    public function cancelSubscription(): JsonResponse
    {
        $userId = auth()->id();

        $subscription = UserSubscription::whereUserId($userId)->first();
        if (!$subscription) {
            return $this->error("User is not subscribed", '', HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        CancelSubscription::dispatch($subscription);

        $latestHistory = SubscriptionHistory::where('user_id', $userId)
            ->latest()
            ->take(12)
            ->get();

        return $this->success([
            'subscription' => new UserSubscriptionResource($subscription),
            'history' => SubscriptionHistoryResource::collection($latestHistory),
        ]);
    }

    public function subscription(): JsonResponse
    {
        $subscription = UserSubscription::where(
            ['user_id' => auth()->id()]
        )->latest('updated_at')->first();

        if (!$subscription) {
            return $this->success([
                'subscription' => [],
                'history' => [],
            ]);
        }

        $latestHistory = SubscriptionHistory::where('user_id', auth()->id())
            ->latest()
            ->take(12)
            ->get();

        return $this->success([
            'subscription' => new UserSubscriptionResource($subscription),
            'history' => SubscriptionHistoryResource::collection($latestHistory),
        ]);
    }

    public function subscriptionHistory(): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(
            SubscriptionHistory::class,
            SubscriptionHistoryResource::class,
            [
                'user_id' => auth()->id(),
            ]
        );
    }

    public function requestConfirmation(): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = auth()->user();

        $confirmation_code = $user->confirmation_code ?? Str::random(30);

        $user->confirmation_code = $confirmation_code;
        $user->save();

        Mail::to($user->email)->queue(new RegisterConfirmationMail($confirmation_code));

        return $this->success([
            'success' => true,
        ]);
    }
}
