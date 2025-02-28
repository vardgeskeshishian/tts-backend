<?php

namespace App\Services\MailerLite;

use App\Contracts\TelegramLoggerContract;
use App\Facades\TelegramLoggerFacade;
use App\Vendor\MailerLiteForked\MailerLite;
use Cache;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserSubscription;

class MailerLiteService
{
    const SUBSCRIBED_TYPE = 'active';
    const UNSUBSCRIBED_TYPE = 'unsubscribed';

    protected $subscribers;
    protected $groups;
    /**
     * @var User|null
     */
    private ?User $user;

    /**
     * MailerLiteService constructor.
     *
     */
    public function __construct()
    {
        $mailerLite = new MailerLite();

        $this->subscribers = $mailerLite->subscribers();
        $this->groups = $mailerLite->groups();
    }

    public function setUser($user)
    {
        if ($user instanceof User) {
            $this->user = $user;

            return $this;
        }

        $this->user = User::find($user);

        return $this;
    }

    /**
     * @return null
     * @throws Exception
     */
    public function addSubscriber()
    {
        if (!$this->user) {
            return null;
        }

        $this->createSubscriber();

        $this->addSubscriberToGroup();

        return null;
    }

    protected function createSubscriber()
    {
        return $this->subscribers->create([
            'email' => $this->user->email,
            'timestamp' => time(),
            'fields' => $this->compileFields(),
        ]);
    }

    /**
     * @throws Exception
     */
    protected function addSubscriberToGroup()
    {
        $subscriber = $this->subscribers->find($this->user->email);

        $this->groups->addSubscriber(config('mailer_lite.group_id'), $subscriber);
    }

    /**
     * @param array $options
     * @return array
     */
    protected function compileFields($options = []): array
    {
        $subscription = UserSubscription::where('user_id', $this->user->id)
            ->latest('updated_at')
            ->first();

        $downloads = $this->user->downloaded;
        $finishedOrders = $this->user->finishedOrders;

        /**
         * @var $lastPurchase Carbon
         * @var $lastFree Carbon
         */
        $lastPurchase = $finishedOrders->sortByDesc('updated_at')->first()->updated_at ?? null;
        $lastFree = $downloads->whereNull('license_id')->sortByDesc('updated_at')->first()->updated_at ?? null;

        $lastPurchase = $lastPurchase ? $lastPurchase->toDateString() : "";
        $lastFree = $lastFree ? $lastFree->toDateString() : "";

        $baseOptions = [
            'company' => $this->user->type->name ?? null,
            'country' => $this->user->country_code,
            'preview_download' => $this->user->previews,
            'free_download' => $downloads->whereNull('license_id')->count(),
            'sub_download' => $downloads->whereNotNull('license_id')->count(),
            'sub_type' => $subscription->license->type ?? null,
            'sub_time' => $subscription->plan->plan ?? null,
            'sub_status' => $subscription->status ?? null, // active, on_hold, canceled
            'purchase_count' => $this->user->bought->count(),
            'purchase_price' => $this->user->bought->sum('price'),
            'last_purchase' => $lastPurchase,
            'last_preview' => $this->user->last_preview_download,
            'last_free' => $lastFree,
        ];

        return array_merge($baseOptions, $options);
    }

    public function updateUser()
    {
        if (!$this->user) {
            return;
        }

        try {
            $subscriber = Cache::remember("ml-subscriber-{$this->user->id}", Carbon::now()->addHour(), fn () => $this->subscribers->find($this->user->email));
        } catch (Exception $e) {
            $subscriber = null;
        }

        $errorMessage = $subscriber['error']['message'] ?? null;

        if ($errorMessage && strtolower($errorMessage) === 'subscriber not found') {
            return;
        }

        if ($errorMessage) {
            TelegramLoggerFacade::pushToChat(TelegramLoggerContract::CHANNEL_DEBUG_ID, "mailer-lite-update", [$subscriber]);
            return;
        }

        if (!$subscriber || !isset($subscriber['id'])) {
            return;
        }

        $this->pushToBatch($this->subscribers->update($subscriber['id'], [
            'fields' => $this->compileFields(),
        ]), 'updateUser');
    }

    public function getSubscriptionStatus(): array
    {
        if (!$this->user) {
            return [
                'subscribed' => false,
                'status' => 'unsubscribed',
            ];
        }

        $key = "mailer-lite-service:get-subscription-status:{$this->user->id}";

        return Cache::remember($key, Carbon::now()->addHour(), function () {
            $response = [
                'subscribed' => false,
                'status' => 'unsubscribed',
            ];

            try {
                $subscriber = $this->subscribers->find($this->user->email);
            } catch (Exception $e) {
                return $response;
            }

            $error = $subscriber['error'] ?? null;

            if ((isset($error) && strtolower($error['message']) === 'subscriber not found')) {
                return $response;
            }

            if ($error) {
                return $response;
            }

            $response['subscribed'] = true;
            $response['status'] = $subscriber['type'] ?? 'subscribed';

            return $response;
        });
    }

    public function setActiveStatus($currentStatus)
    {
        if (!$this->user) {
            return null;
        }

        $subscriber = $this->subscribers->find($this->user->email);

        if ($subscriber['error']['message'] ?? null) {
            \TelegramLoggerFacade::pushToChat(TelegramLoggerContract::CHANNEL_DEBUG_ID, "mailer-lite", [(array)$subscriber]);

            return;
        }

        $this->pushToBatch($this->subscribers->update($subscriber['id'], [
            'type' => $currentStatus,
        ]), 'setActiveStatus');
    }

    public function updateFields($fields = [])
    {
        if (!$this->user) {
            return;
        }

        $subscriber = $this->subscribers->find($this->user->email);

        $error = $subscriber['error'] ?? null;

        if ($error) {
            return;
        }

        if (!$subscriber || !isset($subscriber['id'])) {
            return;
        }

        $this->pushToBatch($this->subscribers->update($subscriber['id'], [
            'fields' => $this->compileFields($fields),
        ]), 'updateFields');
    }

    private function pushToBatch($update, string $option)
    {
        MailerLiteBatcher::getInstance()->pushToBatch(
            $this->user->id,
            $update['method'],
            $update['path'],
            $option,
            $update['data']
        );
    }
}
