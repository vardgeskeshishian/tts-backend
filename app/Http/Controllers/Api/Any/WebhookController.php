<?php

namespace App\Http\Controllers\Api\Any;

use App\Enums\TypeWebhookEnum;
use App\Events\Webhook\Billing\AdjustmentEvent;
use App\Events\Webhook\Billing\SubscriptionEvent;
use App\Events\Webhook\Classic\SubscriptionCanceledEvent;
use App\Events\Webhook\Classic\SubscriptionPaymentFailedEvent;
use App\Events\Webhook\Classic\SubscriptionPaymentRefundedEvent;
use App\Events\Webhook\Classic\SubscriptionPaymentSucceededEvent;
use App\Events\Webhook\Billing\CustomerEvent;
use App\Events\Webhook\Billing\PriceEvent;
use App\Events\Webhook\Billing\ProductEvent;
use App\Events\Webhook\Billing\TransactionEvent;
use App\Events\Webhook\Classic\SubscriptionUpdatedEvent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Webhook\WebhookClassicRequest;
use App\Http\Requests\Webhook\WebhookRequest;
use App\Models\Paddle\Webhook\Webhook;
use Illuminate\Http\JsonResponse;
use Log;

class WebhookController extends ApiController
{
    /**
     * @param WebhookRequest $request
     * @return JsonResponse
     */
    public function webhook(WebhookRequest $request): JsonResponse
    {
        $data = $request->toArray();
        Log::debug('weebhook', [$data]);
        Webhook::create([
            'type' => TypeWebhookEnum::BILLING,
            'data' => json_encode($data)
        ]);

        if (array_key_exists('event_type', $data)) {
            if (str_contains($data['event_type'], 'customer'))
                CustomerEvent::dispatch($data['data']);

            if (str_contains($data['event_type'], 'product'))
                ProductEvent::dispatch($data['data']);

            if (str_contains($data['event_type'], 'price'))
                PriceEvent::dispatch($data['data']);

            if (str_contains($data['event_type'], 'transaction'))
                TransactionEvent::dispatch($data['data']);

            if (str_contains($data['event_type'], 'subscription'))
                SubscriptionEvent::dispatch($data['data']);

            if ($data['event_type'] == 'adjustment.updated'
                && $data['data']['action'] == 'refund'
                && $data['data']['status'] == 'approved')
                AdjustmentEvent::dispatch($data['data']);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * @param WebhookClassicRequest $request
     * @return JsonResponse
     */
    public function webhookClassic(WebhookClassicRequest $request): JsonResponse
    {
        $data = $request->toArray();
        Log::debug('weebhook', [$data]);
        Webhook::create([
            'type' => TypeWebhookEnum::CLASSIC,
            'data' => json_encode($data)
        ]);
        if ($request->verifySign())
        {
            if (array_key_exists('alert_name', $data)) {
                if ($data['alert_name'] == 'subscription_payment_succeeded')
                    SubscriptionPaymentSucceededEvent::dispatch($data);

                if ($data['alert_name'] == 'subscription_payment_failed')
                    SubscriptionPaymentFailedEvent::dispatch($data);

                if ($data['alert_name'] == 'subscription_payment_refunded')
                    SubscriptionPaymentRefundedEvent::dispatch($data);

                if ($data['alert_name'] == 'subscription_cancelled')
                    SubscriptionCanceledEvent::dispatch($data);

                if ($data['alert_name'] == 'subscription_updated')
                    SubscriptionUpdatedEvent::dispatch($data);
            }

            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false
            ], 500);
        }

    }
}
