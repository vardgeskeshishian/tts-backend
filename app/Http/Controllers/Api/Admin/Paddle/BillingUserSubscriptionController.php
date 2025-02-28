<?php

namespace App\Http\Controllers\Api\Admin\Paddle;

use App\Events\GetSubscriptionsFromPaddleBillingEvent;
use App\Http\Controllers\Api\ApiController;
use App\Services\PaddleApiService;
use Illuminate\Http\JsonResponse;

class BillingUserSubscriptionController extends ApiController
{
    public function __construct(
        public PaddleApiService $paddleApiService
    )
    {
        parent::__construct();
    }

    public function getSubscriptionsFromPaddle(): JsonResponse
    {
        GetSubscriptionsFromPaddleBillingEvent::dispatch();

        return response()->json([
            'message' => __('Request to receive subscribers from Paddle has been sent')
        ]);
    }
}
