<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaddleApiKey;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Log;

class PaddleService
{
    protected $vendorAuthCode;
    protected $vendorId;
    /**
     * @var AnalyticsService
     */
    private AnalyticsService $analyticsService;
    private string $host;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $key = PaddleApiKey::where('type_key', 'classic')->first();
        $this->vendorAuthCode = $key->key;
        $this->vendorId = $key->vendor_id;
        $this->host = 'https://vendors.paddle.com/';
    }

    public function checkout(Order $order)
    {
        $data = [];
        $data['vendor_id'] = $this->vendorId;
        $data['vendor_auth_code'] = $this->vendorAuthCode;

        $data['title'] = "TakeTones Order {$order->id}";

        $data['webhook_url'] = route('webhook.paddle');

        $data['custom_message'] = $order->items->map(function (OrderItem $item) {
            return $item->getItemName() . "({$item->license->type})";
        })->implode(', ');

        $env = config('app.url') === 'https://stage-backend.taketones.com' ? 'stage' : 'prod';

        $data['passthrough'] = $data['custom_message'] . '|oi:' . $order->id . '|_ga:' . (request()->cookie()['_ga'] ?? null) . "|env:$env";

        if (auth()->check() && auth()->user()->country_code) {
            $data['customer_country'] = auth()->user()->country_code;
        }

        $data['image_url'] = 'https://static.taketones.com/images/custom_checkout_logo.png';
        $data['quantity_variable'] = 0;
        $data['discountable'] = 0;

        $data['customer_email'] = auth()->user()->email;

        $promocode = $order->promocodeObject;

        if ($promocode) {
            $promocodeInfo = [
                'prices' => [],
                'tempPrice' => 0,
            ];

            foreach ($order->items as $item) {
                $key = $item->item_type;

                $promocodeInfo['prices'][$key][$item->getItemId()] = $promocode->returnPriceWithDiscountForOrderItem($item);

                $promocodeInfo['tempPrice'] += $promocode->returnPriceWithDiscountForOrderItem($item);
            }

            $totalPrice = $promocodeInfo['tempPrice'];
        } else {
            $totalPrice = $order->total;
        }

        if (auth()->check() && auth()->user()->isAdmin) {
            $totalPrice = 0;
            $data['title'] = "TakeTones Admin Test Order";
        }

        $data['prices'] = [
            'USD:' . $totalPrice,
        ];

        $result = Curl::to('https://vendors.paddle.com/api/2.0/product/generate_pay_link')
            ->withData($data)
            ->asJson()
            ->post();

        if ($result->success) {
            return $result->response->url;
        } else {
            return null;
        }
    }

    /**
     * @param UserSubscription $subscription
     */
    public function cancel(UserSubscription $subscription)
    {
        $data = [];
        $data['vendor_id'] = $this->vendorId;
        $data['vendor_auth_code'] = $this->vendorAuthCode;

        $data['subscription_id'] = $subscription->subscription_id;

        $result = Curl::to($this->host.'api/2.0/subscription/users_cancel')
            ->withData($data)
            ->asJson()
            ->post();

        if ($result->success) {
            $subscription->cancelled_at_paddle = Carbon::now();
            $subscription->save();
        } else {
            Log::error(json_encode($result, true));
        }
    }

    public function getSubscribedUsers(?string $email = null)
    {
        $data = [];
        $data['vendor_id'] = $this->vendorId;
        $data['vendor_auth_code'] = $this->vendorAuthCode;

        $result = Curl::to('https://vendors.paddle.com/api/2.0/subscription/users')
            ->withData($data)
            ->asJson()
            ->post();

        if ($result->success) {
            if (!$email) {
                return $result->response;
            }

            foreach ($result->response as $item) {
                if ($item->user_email === $email) {
                    return $item;
                }
            }
            return $result->response;
        }

        return $result;
    }
}
