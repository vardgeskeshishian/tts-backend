<?php


namespace App\Services;

use App\Services\DTO\TransactionDTO;
use App\Facades\TelegramLoggerFacade;
use Throwable;
use App\Models\User;
use App\Models\Order;
use App\Models\Track;
use Ramsey\Uuid\Uuid;
use App\Models\OrderItem;
use App\Models\TrackAudio;
use App\Constants\UserEnv;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\AnalyticsSendEvent;
use App\Models\UserSubscription;
use App\Jobs\GoogleTransactionSend;
use Irazasyed\LaravelGAMP\Facades\GAMP;
use App\Contracts\TelegramLoggerContract;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class AnalyticsService
{
    /**
     * @var string
     */
    private $cid;
    /**
     * @var Request
     */
    private $request;

    public function setGA($clientId = null)
    {
        $this->cid = $this->getClientId($clientId);

        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function sendSubscribeTransaction(User $user, Request $request, $clientId = null)
    {
        $cid = $this->getClientId($clientId);

        // is uuid is valid - then it means that cid is not and from google
        $gaClientIDSet = !Uuid::isValid($cid);

        $clientIdInfo = [
            'cid' => $cid,
        ];

        if ($user) {
            $clientIdInfo['uid'] = (string) $user->id;
            $clientIdInfo['cd3'] = (string) $user->id;
        }

        if (!$gaClientIDSet) {
            $clientIdInfo = array_merge($clientIdInfo, [
                'cn' => 'none',
                'cs' => 'manual',
                'cm' => 'none',
                'ck' => 'not set',
            ]);
        }

        $orderId = $request->get('order_id');
        $coupon = $request->get('coupon');

        if ($coupon !== "") {
            $orderId .= " ($coupon)";
        }

        $sameTransactionsData = [
            'v' => 1,
            'tid' => config('gamp.tracking_id'),
            'geoid' => $user->country_code,
            'ti' => $orderId,
        ];

        $sameTransactionsData = array_merge($sameTransactionsData, $clientIdInfo);

        $dto = new TransactionDTO($request);

        $transactionData = array_merge($sameTransactionsData, [
            't' => 'transaction',
            'tr' => $dto->getEarnings(),
            'ts' => 0,//$dto->getTax(),
            'tt' => $dto->getFee(),
            'cu' => 'USD',
        ]);

        $items = [];

        $items[] = array_merge($sameTransactionsData, [
            't' => 'item',
            'in' => $request->get('plan_name'),
            'ip' => (string) $dto->getEarnings(),
            'cu' => 'USD',
            'iq' => 1,
            'ic' => $request->get('passthrough'),
            'iv' => $request->get('instalments'),
        ]);

        $fullTransaction = http_build_query($transactionData);
        foreach ($items as $item) {
            $fullTransaction .= "\r\n" . http_build_query($item);
        }

        GoogleTransactionSend::dispatch(
            "https://www.google-analytics.com/batch",
            "get",
            $fullTransaction
        );
    }

    public function sendSubscribeRefundTransaction(
        UserSubscription $subscription,
        User $user,
        Request $request,
        $clientId = null
    ) {
        $orderId = $request->get('order_id');
        $refundType = $request->get('refund_type');

        if ($refundType !== 'full') {
            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_ANALYTICS_ID,
                "Refund of type {$refundType} was requested for subscription #{$orderId}",
            );

            return;
        }

        $cid = $this->getClientId($clientId);

        // is uuid is valid - then it means that cid is not and from google
        $gaClientIDSet = !Uuid::isValid($cid);

        $clientIdInfo = [
            'cid' => $cid,
        ];

        if ($user) {
            $clientIdInfo['uid'] = (string) $user->id;
            $clientIdInfo['cd3'] = (string) $user->id;
        }

        if (!$gaClientIDSet) {
            $clientIdInfo = array_merge($clientIdInfo, [
                'cn' => 'none',
                'cs' => 'manual',
                'cm' => 'none',
                'ck' => 'not set',
            ]);
        }

        $coupon = $request->get('coupon');

        if ($coupon !== "") {
            $orderId .= " (${coupon})";
        }

        $sameTransactionsData = [
            'v' => 1,
            'tid' => config('gamp.tracking_id'),
            'geoid' => $user->country_code,
            'ti' => $orderId,
        ];

        $sameTransactionsData = array_merge($sameTransactionsData, $clientIdInfo);

        $dto = new TransactionDTO($request);

        $transactionData = array_merge($sameTransactionsData, [
            't' => 'transaction',
            'tr' => (string) ($dto->getDecrease() * -1),
            'ts' => 0,//$dto->getTax(),
            'tt' => $dto->getFee(),
            'cu' => 'USD',
        ]);

        $items = [];

        $planName = $subscription->license->type;

        $planName .= match (strtolower($subscription->plan->plan)) {
            '1 month' => ' (monthly)',
            '12 months' => ' (yearly)',
            default => ' (' . strtolower($subscription->plan->plan) . ')',
        };

        $items[] = array_merge($sameTransactionsData, [
            't' => 'item',
            'in' => $planName,
            'ip' => (string) $dto->getDecrease(),
            'cu' => 'USD',
            'iq' => -1,
            'ic' => $request->get('passthrough'),
            'iv' => $request->get('instalments'),
        ]);

        $fullTransaction = http_build_query($transactionData);
        foreach ($items as $item) {
            $fullTransaction .= "\r\n" . http_build_query($item);
        }

        GoogleTransactionSend::dispatch(
            "https://www.google-analytics.com/batch",
            "get",
            $fullTransaction
        );
    }

    public function sendBuyTransaction(Order $order, User $user, $clientId = null, $request = null)
    {
        $request = $request ?? request();

        $cid = $this->getClientId($clientId);

        // is uuid is valid - then it means that cid is not and from Google
        $gaClientIDSet = !Uuid::isValid($cid);

        $clientIdInfo = [
            'cid' => $cid,
        ];

        if ($user) {
            $clientIdInfo['uid'] = (string) $user->id;
            $clientIdInfo['cd3'] = (string) $user->id;
        }

        if (!$gaClientIDSet) {
            $clientIdInfo = array_merge($clientIdInfo, [
                'cn' => 'none',
                'cs' => 'manual',
                'cm' => 'none',
                'ck' => 'not set',
            ]);
        }

        $sameTransactionsData = [
            'v' => 1,
            'tid' => config('gamp.tracking_id'),
            'geoid' => $user->country_code,
            'ti' => $order->id,
        ];

        $sameTransactionsData = array_merge($sameTransactionsData, $clientIdInfo);

        $dto = new TransactionDTO($request);

        $transactionData = array_merge($sameTransactionsData, [
            't' => 'transaction',
            'tr' => $dto->getEarnings(),
            'ts' => 0,//$dto->getTax(),
            'tt' => $dto->getFee(),
            'cu' => 'USD',
        ]);

        $items = [];
        $total = 0;

        $promoCode = $order->promocodeObject;

        $promoCodeTrackName = $promoCode ? " ({$promoCode->code})" : "";

        try {

            /**
             * @var $track Track
             */
            foreach ($order->items as $item) {
                [$category, $self_duet] = $item->getAnalyticsAdditionalInformation();

                $price = $promoCode ? $promoCode->returnPriceWithDiscountForOrderItem($item) : $item->price;
                $price = $dto->calculateCleanPrice($price);

                $currentItemData = [
                    'type' => 'item',
                    'name' => $item->getItemName() . $self_duet . $promoCodeTrackName,
                    'category' => $category['name'],
                    'price' => $price,
                    'quantity' => 1,
                    'license' => $item->license_number,
                ];

                $items[] = array_merge($sameTransactionsData, [
                    't' => 'item',
                    'in' => $currentItemData['name'],
                    'ip' => (string) $currentItemData['price'],
                    'cu' => 'USD',
                    'iq' => $currentItemData['quantity'],
                    'ic' => $currentItemData['license'],
                    'iv' => $currentItemData['category'],
                ]);

                $total += $price;
            }

            $transactionData['tr'] = $total;

            $fullTransaction = http_build_query($transactionData);
            foreach ($items as $item) {
                $fullTransaction .= "\r\n" . http_build_query($item);
            }

            if ($user->role !== UserEnv::ROLE_ADMIN) {
                GoogleTransactionSend::dispatch(
                    "https://www.google-analytics.com/batch",
                    "get",
                    $fullTransaction
                );
            }

            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_ANALYTICS_ID,
                $user->role === UserEnv::ROLE_ADMIN
                    ? "Emulating google transaction: user is admin"
                    : "Sending google transaction",
                [
                    'transaction-data' => $transactionData,
                    'items' => $items,
                ]
            );
        } catch (Throwable $e) {
            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_CRITICAL_ID,
                "Can't send google transaction",
                [
                    'message' => $e->getMessage(),
                    'trace' => array_slice($e->getTrace(), 0, 5),
                ]
            );
        }
    }

    public function sendRefundTransaction(Order $order)
    {
        if ($this->request->has('refund_type') && $this->request->get('refund_type') !== 'full') {
            $orderId = $this->request->get('order_id');
            $refundType = $this->request->get('refund_type');

            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_ANALYTICS_ID,
                "[OrderRefund]: refund of type {$refundType} was requested for order {$orderId}"
            );

            return;
        }

        // is uuid is valid - then it means that cid is not and from google
        $gaClientIDSet = !Uuid::isValid($this->cid);

        $clientIdInfo = [
            'cid' => $this->cid,
        ];

        if ($order->user_id) {
            $clientIdInfo['uid'] = (string) $order->user_id;
            $clientIdInfo['cd3'] = (string) $order->user_id;
        }

        if (!$gaClientIDSet) {
            $clientIdInfo = array_merge($clientIdInfo, [
                'cn' => 'none',
                'cs' => 'manual',
                'cm' => 'none',
                'ck' => 'not set',
            ]);
        }

        $sameTransactionsData = [
            'v' => 1,
            'tid' => config('gamp.tracking_id'),
            'geoid' => $order->user->country_code,
            'ti' => $order->id,
        ];

        $sameTransactionsData = array_merge($sameTransactionsData, $clientIdInfo);

        $transactionData = array_merge($sameTransactionsData, [
            't' => 'transaction',
            'cu' => 'USD',
        ]);

        $items = [];
        $total = 0;

        $promoCode = $order->promocodeObject;

        $promoCodeTrackName = $promoCode
            ? " ({$promoCode->code})"
            : "";

        $dto = new TransactionDTO(request());
        /**
         * @var $track Track
         */
        foreach ($order->items as $item) {
            [$category, $self_duet] = $item->getAnalyticsAdditionalInformation();

            $price = $promoCode ? $promoCode->returnPriceWithDiscountForOrderItem($item) : $item->price;

            $price = $dto->calculateCleanPrice($price);

            $items[] = array_merge($sameTransactionsData, [
                't' => 'item',
                'in' => $item->getItemName() . $self_duet . $promoCodeTrackName,
                'ip' => (string) $price,
                'cu' => 'USD',
                'iq' => -1,
                'ic' => $item->license_number,
                'iv' => $category['name'],
            ]);

            $total += $price;
        }

        $transactionData['tr'] = (string) ($total > 0 ? $total * -1 : $total);

        $fullTransaction = http_build_query($transactionData);
        foreach ($items as $item) {
            $fullTransaction .= "\r\n" . http_build_query($item);
        }

        GoogleTransactionSend::dispatch(
            "https://www.google-analytics.com/batch",
            "get",
            $fullTransaction
        );

        TelegramLoggerFacade::pushToChat(
            TelegramLoggerContract::CHANNEL_ANALYTICS_ID,
            "Sending google refund transaction",
            [
                'transaction-data' => $transactionData,
                'items' => $items,
            ]
        );
    }

    private function getClientId($clientId = null)
    {
        if (!request()->cookie() || empty(request()->cookie())) {
            $cookie = [];
        } else {
            $cookie = request()->cookie();
        }

        $fullGa = $cookie['_ga'] ?? $clientId;

        $cid = null;

        if ($fullGa) {
            try {
                [, , $cid1, $cid2] = explode('.', $fullGa, 4);
                $cid = $cid1 . '.' . $cid2;
            } catch (\Exception $e) {
            }
        }

        if (!$cid) {
            // Create a new UUID which is used as the Client ID
            $cid = $clientId ?? (string)Str::uuid();
        }

        return $cid;
    }

    private function sendEvent($category, $action, $label, $value = null, $clientId = null)
    {
        /**
         * @var $gamp Analytics
         */
        $gamp = GAMP::setClientId($this->getClientId($clientId));

        if (auth()->check() && auth()->user()->country_code) {
            $gamp->setGeographicalOverride(auth()->user()->country_code);
        }
        if (auth()->check() && auth()->user()) {
            $userId = (string) auth()->user()->id;

            $gamp->setUserId($userId);
            $gamp->setCustomDimension($userId, 3);
            // $gamp->setCustomDimension('3', $userId);
            // $gamp->setCustomDimension('cd3', $userId);
        }

        $gamp->setEventCategory($category);
        $gamp->setEventAction($action);
        if (!empty($label)) {
            $gamp->setEventLabel($label);
        }
        if (!empty($value)) {
            $gamp->setEventValue($value);
        }

        if ($category !== 'query') {
            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_ANALYTICS_ID,
                "Sending google event",
                [
                    '_ga' => $this->getClientId($clientId),
                    'category' => $category,
                    'action' => $action,
                    'label' => $label,
                    'value' => $value,
                    'eventValue' => $gamp->getEventValue(),
                    'url' => $gamp->getUrl(),
                ]
            );
        }

        AnalyticsSendEvent::dispatch($gamp);
    }

    public function sendPreviewDownload(TrackAudio $audio)
    {
        $this->sendEvent(
            'preview',
            $audio->preview_name,
            $audio->track->analytics_name
        );
    }

    public function sendRegistration(User $user, $label)
    {
        $type = optional($user->type)->name;

        $type = strtolower(explode(' ', $type)[0]);

        $this->sendEvent(
            'registration',
            $type,
            $label
        );
    }

    public function sendFreeDownload()
    {
        $this->sendEvent(
            'free_license',
            'free_download',
            ''
        );
    }

    // when user adds something to cart
    public function sendAddToCart(OrderItem $item)
    {
        $this->sendEvent('buy', 'addtocart', $item->getAnalyticsName());
    }

    // when user removes something from cart
    public function sendRemoveFromCart(OrderItem $item)
    {
    }

    // when user downloads something while being subscribed
    public function sendSubDownload($label = "")
    {
        $this->sendEvent(
            'subscription',
            'subs_download',
            $label
        );
    }

    // when subscription webhook is received
    public function sendSubscriptionEvent($action, $label = "", $value = null, $ga = null)
    {
        $this->sendEvent(
            'subscription',
            $action,
            $label,
            $value,
            $ga
        );
    }

    public function sendSearchResultSizeEvent($action, $label, $value = null, $ga = null)
    {
        $this->sendEvent(
            'query',
            $action,
            $label,
            $value,
            $ga ?? 'GA1.2.1300246083.12345789',
        );
    }

    /**
     * @param $passthrough
     * @return array returns an array containing [orderId, ga]
     */
    public function findInformationFromPassthrough($passthrough): array
    {
        preg_match('/oi:(\d+)/', $passthrough, $matches);

        $orderId = count($matches) > 0 ? $matches[1] : null;

        preg_match('/(_ga:(.*)\|)|(_ga:(.*)$)/', $passthrough, $gaMatches);

        if (($_ga = $gaMatches[count($gaMatches) - 1] ?? null) && empty($_ga)) {
            $_ga = null;
        }

        if ($_ga === "") {
            $_ga = null;
        }

	logs('telegram-debug')->debug('analytics-passthrough-parser', [
		'pass' => $passthrough,
		'matches' => $gaMatches,
	]);

        return [$orderId, $_ga];
    }
}
