<?php

namespace App\Factories;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Constants\Env;
use App\Mail\PurchaseMail;
use App\Libs\PartnerProgram;
use Illuminate\Http\Request;
use App\Jobs\FillAuthorBalanceJob;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Mail;
use App\Services\PartnerProgramService;
use App\Services\Finance\BalanceService;

class SubscriptionFactory
{
    /**
     * @var array|Request|string
     */
    protected $ga;

    /**
     * @var AnalyticsService
     */
    private $analyticsService;
    protected $userId;
    protected $subscriptionId;
    protected $subscriptionPlanId;
    /**
     * @var mixed|null
     */
    private $orderId;
    /**
     * @var User
     */
    private $user;

    /**
     * SubscriptionFactory constructor.
     *
     * @param AnalyticsService $analyticsService
     */
    public function __construct(
        AnalyticsService $analyticsService
    ) {
        $this->analyticsService = $analyticsService;
    }

    public function work()
    {
        $alert = request()->get('alert_name');

        $pass = json_decode(request('passthrough'), true);

        if (is_array($pass)) {
            $this->userId = $pass['user_id'] ?? null;
            $this->ga = $pass['_ga'] ?? null;
        }

        if (is_numeric($pass)) {
            $this->userId = $pass;
        }

        if ($this->userId) {
            $this->user = User::find($this->userId);
        }

        logs('telegram-debug')->debug('info-' . $alert, [
            'p' => $pass,
            'u' => $this->userId,
            'g' => $this->ga,
            'r' => request()->all(),
        ]);

        $passthrough = explode('|', request()->get('passthrough'));
        $keyVal = [];
        foreach($passthrough as $vals) {
            $e = explode(':', $vals);
            if (count($e) === 1) {
                continue;
            }

            $keyVal[$e[0]] = $e[1];
        }

        if (isset($keyVal['env'])) {
            switch ($keyVal['env']) {
                case 'stage':
                    if (config('app.url') !== 'https://stage-backend.taketones.com') {
                        return;
                    }
                    break;
                default:
                    if (config('app.url') !== 'https://api.taketones.com') {
                        return;
                    }
            }
        }

        $this->subscriptionId = request('subscription_id');
        $this->subscriptionPlanId = request('subscription_plan_id');

        switch ($alert) {
            case 'payment_succeeded':
                $this->findOrderId();
                $this->handlePaymentSucceeded();
                break;
            case 'payment_refunded':
                $this->findOrderId();
                $this->handlePaymentRefund();
                break;
        }
    }

    private function handlePaymentSucceeded()
    {
        $order = Order::find($this->orderId);

        $order->succeeded_at = Carbon::now();
        $order->receipt_url = request('receipt_url');
        $order->status = Env::STATUS_FINISHED;
        $order->save();

        smart_dispatcher((new FillAuthorBalanceJob())->setOrder($order), [BalanceService::class]);

        $user = $order->user;

        PartnerProgramService::writeEarnings(
            $user,
            $order->getTotalWithPromocode(),
            PartnerProgram::EARNING_SOURCE_ORDER,
            $order->id
        );

        Mail::to($user->email)->queue(new PurchaseMail($order->id));

        $this->analyticsService->sendBuyTransaction($order, $user, $this->ga);
    }

    private function handlePaymentRefund()
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            logs('telegram-debug')->error('payment-refund:order-not-found', request()->all());
            return;
        }

        $order->status = Env::STATUS_REFUNDED;
        $order->refunded_at = Carbon::now();
        $order->save();

        $this->analyticsService
            ->setGA($this->ga)
            ->setRequest(request())
            ->sendRefundTransaction($order);

        PartnerProgramService::withdrawEarnings(
            $order->user,
            request('earnings_decrease'),
            PartnerProgram::SOURCE_ORDER_REFUND
        );
    }

    private function findOrderId()
    {
        [$this->orderId, $this->ga] = $this->analyticsService->findInformationFromPassthrough(request()->get('passthrough'));

        if ($this->orderId) {
            $order = Order::find($this->orderId);
            
            $this->user = $this->user ?? $order->user;
        }

        return $this;
    }
}
