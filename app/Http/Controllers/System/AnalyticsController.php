<?php


namespace App\Http\Controllers\System;

use App\Http\Controllers\Api\ApiController;
use App\Jobs\GoogleTransactionSend;
use App\Models\Order;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AnalyticsController extends ApiController
{
    /**
     * @var AnalyticsService
     */
    private AnalyticsService $analyticsService;

    /**
     * AnalyticsController constructor.
     * @param AnalyticsService $analyticsService
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        parent::__construct();

        $this->analyticsService = $analyticsService;
    }

    /**
     * @return View
     */
    public function sendFormView(): View
    {
        return view('admin.analytics.send');
    }

    public function sendAction(Request $request): void
    {
        $data = $request->get('data');

        $json = json_decode($data, true);

        $newRequest = new Request();
        $newRequest->merge($json);

        switch ($newRequest->get('alert_name')) {
            case 'payment_succeeded':
                $this->sendBuyTransactionFromRequest($newRequest);
                break;
            case 'subscription_payment_refunded':
                $this->sendSubscriptionPaymentRefundedFromRequest($newRequest);
                break;
            case 'subscription_payment_succeeded':
                $this->sendSubscriptionPaymentSucceededFromRequest($newRequest);
                break;
            default:
        }
    }

    private function sendSubscriptionPaymentSucceededFromRequest(Request $request)
    {
        $pass = json_decode($request->get('passthrough'), true);

        if (is_array($pass)) {
            $userId = $pass['user_id'];
            $ga = $pass['_ga'] ?? null;

            $user = User::find($userId);

            $this->analyticsService->sendSubscribeTransaction($user, $request, $ga);
        }
    }

    private function sendBuyTransactionFromRequest(Request $request)
    {
        [$orderId, $_ga] = $this->analyticsService->findInformationFromPassthrough($request->get('passthrough'));

        $order = Order::find($orderId);
        $user = $order->user;

        $this->analyticsService->sendBuyTransaction($order, $user, $_ga, $request);
    }

    private function sendSubscriptionPaymentRefundedFromRequest(Request $request)
    {
        $subscription = UserSubscription::where('subscription_id', $request->subscription_id)->first();

        $pass = json_decode($request->get('passthrough'), true);

        if (is_array($pass)) {
            $userId = $pass['user_id'];
            $ga = $pass['_ga'] ?? null;

            $user = User::find($userId);

            $this->analyticsService->sendSubscribeRefundTransaction($subscription, $user, $request, $ga);
        }
    }

    public function sendGoogleTransaction(Request $request)
    {
        $transactionData = $request->get('transaction_data');
        $items = $request->get('items');

        $transactionData = json_decode($transactionData, true);
        $items = json_decode($items, true);

        $fullTransaction = http_build_query($transactionData);
        foreach ($items as $item) {
            $fullTransaction .= "\r\n" . http_build_query($item);
        }

        GoogleTransactionSend::dispatch(
            "https://www.google-analytics.com/batch",
            "get",
            $fullTransaction
        );

        return redirect()->back();
    }
}
