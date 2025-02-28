<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Jobs\CancelSubscription;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CancelSubscriptionRequest;
use App\Http\Resources\Authorized\UserSubscriptionResource;
use App\Models\UserSubscription;
use App\Services\PaddleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends ApiController
{

    public function __construct(
        private readonly PaddleService $paddleService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/subscriptions/classic/cancel",
     *     summary="Cancel subscription classic",
     *     tags={"Subscription"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *           @OA\Property(property="subscription_id", type="string", description="Subscription ID (Paddle)"),
     *           @OA\Property(property="cancel_at", type="string", description="Cancel datetime"),
     *      ))),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     *
     * @param CancelSubscriptionRequest $request
     * @return JsonResponse
     */
    public function cancelClassic(CancelSubscriptionRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('subscription_id', $request->getSubscriptionId())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }

        if ($subscription->status == 'past_due')
        {
            $this->paddleService->cancel($subscription);
            $subscription->update([
                'status' => 'deleted',
                'cancelling_at' => Carbon::now()
            ]);
        } else {
            $subscription->update([
                'status' => 'active until',
                'cancelling_at' => $request->getCancelAt()
            ]);
            CancelSubscription::dispatch($subscription)->delay(Carbon::parse($request->getCancelAt())->subHours(2));
        }

        return response()->json(new UserSubscriptionResource($subscription));
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/subscriptions/classic/resume/{subscription_id}",
     *     summary="Resume subscription classic",
     *     tags={"Subscription"},
     *     @OA\Parameter(parameter="subscription_id", description="Subscription id", required=true, in="path", name="subscription_id"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     *
     * @param string $subscription_id
     * @return JsonResponse
     */
    public function resumeClassic(string $subscription_id): JsonResponse
    {
        $user = auth()->user();

        try {
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('subscription_id', $subscription_id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }

        $subscription->update([
            'status' => 'active',
            'cancelling_at' => null
        ]);

        return response()->json(new UserSubscriptionResource($subscription));
    }
}