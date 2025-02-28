<?php


namespace App\Services\Finance;


use App\Constants\Env;
use App\Constants\FinancesEnv;
use App\Constants\SubmissionsEnv;
use App\Exceptions\AuthorsNonApplicableException;
use App\Helpers\Finance\RateCalculator;
use App\Libs\OptionsLib;
use App\Models\Finance\Balance;
use App\Models\Finance\DetailedBalance;
use App\Models\Options;
use App\Models\OrderItem;
use App\Models\Track;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\UserSubscription;
use App\Models\VideoEffects\VideoEffect;
use Carbon\Carbon;

class BalanceService
{
    const DEFAULT_PERCENTAGE = 50;
    /**
     * @var User
     */
    private $user;
    private bool $isPartner;
    private bool $isAuthor;
    private ?float $award;
    private $date = null;
    private $sourceType;
    private ?int $sourceId;
    private ?float $percentage;
    /**
     * @var DetailedBalance
     */
    private $detailedBalance;
    /**
     * @var OrderItem|UserDownloads
     */
    private $source;
    private int $originalPrice;
    /**
     * @var User|null
     */
    private $buyer;
    private $wasSubscriptionActive = false;
    private $subscriptionId = null;
    /**
     * @var int|mixed|null
     */
    private $partnerLinkId;
    /**
     * @var Balance
     */
    private Balance $balance;
    /**
     * @var mixed
     */
    private $rate = null;
    private ?float $discount = null;
    /**
     * @var BalanceStatsService
     */
    private BalanceStatsService $statsService;

    public function __construct(BalanceStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function setDate(string $date = null)
    {
        $this->date = $date ? Carbon::parse($date)->format(FinancesEnv::BALANCE_DATE_FORMAT) : null;

        return $this;
    }

    /**
     * @param $userId
     * @param $buyerId
     * @param $sourceType
     * @param int|null $sourceId
     *
     * @throws AuthorsNonApplicableException
     */
    public function award($userId, $buyerId, $sourceType, ?int $sourceId): void
    {
        $this->setUser($userId);

        if (!$this->isAuthor && !$this->isPartner) {
            return;
        }

        $this->statsService->setUser($this->user);

        $this->setBuyer($buyerId)
            ->setConfig($sourceType, $sourceId)
            ->getBalanceForDate()
            ->getSource();

        if ($this->sourceType === FinancesEnv::SOURCE_TYPE_ORDER_ITEM
            && (!in_array($this->source->item_type, [Env::ITEM_TYPE_TRACKS, Env::ITEM_TYPE_VIDEO_EFFECTS]))) {
            return;
        }

        if ($this->isAuthor && $this->sourceType !== FinancesEnv::SOURCE_TYPE_P_SUBSCRIPTION) {
            $this->awardAuthor();
        }

        if ($this->isPartner) {
            $this->awardPartner();
        }
    }

    private function setUser($userId)
    {
        $this->user = User::find($userId);

        $this->isPartner = $this->user->isPartner();
        $this->isAuthor = $this->user->isAuthor();

        return $this;
    }

    private function getSource()
    {
        switch ($this->sourceType) {
            case FinancesEnv::SOURCE_TYPE_ORDER_ITEM:
                $this->source = OrderItem::find($this->sourceId);
                $order = $this->source->order;
                $promocodeObject = $order->promocodeObject;

                $this->discount = $promocodeObject->discount ?? null;
                if (am_i_on_stage()) {
                    $this->originalPrice = $this->source->price;
                } else {
                    $this->originalPrice = $promocodeObject
                        ? $promocodeObject->returnPriceWithDiscountForOrderItem($this->source)
                        : $this->source->price;
                }
                break;
            case FinancesEnv::SOURCE_TYPE_A_DOWNLOAD:
                $this->source = UserDownloads::find($this->sourceId);
                $this->originalPrice = 0;
                break;
            case FinancesEnv::SOURCE_TYPE_P_SUBSCRIPTION:
                $this->source = UserSubscription::find($this->sourceId);
                $this->originalPrice = $this->source->license->price;
                break;
        }

        return $this;
    }

    private function getBalanceForDate(): BalanceService
    {
        $this->balance = Balance::firstOrCreate([
            'user_id' => $this->user->id,
            'date' => $this->date,
            'status' => 'awaiting',
        ]);

        if ($this->balance->payment_email) {
            return $this;
        }

        $prevBalance = Balance::where('user_id', $this->user->id)
            ->whereNotNull('payment_email')
            ->first();

        if ($prevBalance && !$this->balance->payment_email) {
            $this->balance->payment_email = $prevBalance->payment_email;
            $this->balance->payment_type = $prevBalance->payment_type;
            $this->balance->save();
        }

        return $this;
    }

    private function setConfig($sourceType, ?int $sourceId)
    {
        $this->date = $this->date ?: Carbon::now()->format(FinancesEnv::BALANCE_DATE_FORMAT);

        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
        $this->percentage = self::DEFAULT_PERCENTAGE;

        return $this;
    }

    /**
     * @param $buyerId
     *
     * @return $this
     * @throws AuthorsNonApplicableException
     */
    private function setBuyer($buyerId): self
    {
        $this->buyer = User::find($buyerId);

        $env = config('app.url') === 'https://stage-backend.taketones.com' ? 'stage' : 'prod';

        if ($env === 'prod' && ($this->buyer->isPartner() || $this->buyer->isAuthor())) {
            throw new AuthorsNonApplicableException("buyer: {$buyerId} is either author or partner");
        }

        if (!$this->buyer) {
            return $this;
        }

        $subscription = $this->buyer->subscription;

        $this->subscriptionId = $subscription->id ?? null;
        $this->wasSubscriptionActive = $subscription && $subscription->isActive();
        $this->partnerLinkId = optional($this->buyer->invitee)->link_id;

        return $this;
    }

    private function awardAuthor()
    {
        $this->addDetails(FinancesEnv::USER_TYPE_AUTHOR);
    }

    /** @noinspection Annotator
     * @noinspection Annotator
     */
    private function addDetails($userType)
    {
        $this->calculateAward($userType);

        switch ($userType) {
            case FinancesEnv::USER_TYPE_PARTNER:
                $this->balance->increment('partner_balance', $this->award);
                break;
            case FinancesEnv::USER_TYPE_AUTHOR:
                $this->balance->increment('author_balance', $this->award);
                break;
        }

        $this->balance->saveQuietly();

        $this->detailedBalance = DetailedBalance::where([
            'balance_id' => $this->balance->id,
            'source_id' => $this->sourceId,
            'source_type' => $this->sourceType,
            'user_type' => $userType,
        ])->first();

        $sourceType = $this->source->item_type ?? $this->source->type;
        if (in_array(strtolower($sourceType), ['creator', 'business'])) {
            $sourceType = Env::ITEM_TYPE_TRACKS;
        }

        if ($this->detailedBalance) {
            if ($this->detailedBalance->item_type === null) {
                $this->detailedBalance->item_type = $sourceType;
            }

            return true;
        }

        $this->detailedBalance = DetailedBalance::create([
            'balance_id' => $this->balance->id,
            'source_id' => $this->sourceId,
            'source_type' => $this->sourceType,
            'percentage' => $this->rate,
            'award' => $this->award,
            'buyer_id' => $this->buyer->id,
            'license_id' => $this->source->license_id,
            'original_price' => $this->originalPrice,
            'was_subscription_active' => $this->wasSubscriptionActive,
            'subscription_id' => $this->subscriptionId,
            'partner_link' => $this->partnerLinkId,
            'user_type' => $userType,
            'track_id' => $this->source->item_id ?? $this->source->track_id,
            'item_type' => $sourceType,
            'rate' => $this->rate,
            'discount' => $this->discount,
            'country_code' => optional(User::findOrFail($this->buyer->id))->country_code ?? 'not-set',
            'created_at' => $this->source->created_at,
            'updated_at' => $this->source->updated_at,
        ]);
    }

    private function calculateAward($userType)
    {
        $percentage = $this->percentage;

        switch ($userType) {
            case FinancesEnv::USER_TYPE_PARTNER:
                $percentage = Options::getOptionValue(OptionsLib::OPTION_PARTNER_AWARD);

                $this->rate = $percentage;

                break;
            case FinancesEnv::USER_TYPE_AUTHOR:
                $this->calculateRate();
                break;
        }

        if ($this->sourceType === FinancesEnv::SOURCE_TYPE_P_SUBSCRIPTION) {
            $this->award = $this->originalPrice * ($percentage / 100);

            return $this;
        }

        if ($this->sourceType === FinancesEnv::SOURCE_TYPE_A_DOWNLOAD && $this->source->license_id !== null) {
            $this->award = 0;

            return $this;
        }

        $this->award = $this->originalPrice === 0
            ? 0
            : ($percentage / 100) * (($this->originalPrice) - ($this->originalPrice * 0.14));

        return $this;
    }

    private function calculateRate()
    {
        $item = match ($this->source?->item_type ?? $this->source?->type) {
            Env::ITEM_TYPE_VIDEO_EFFECTS => VideoEffect::find($this->source?->item_id ?? $this->source->track_id),
            default => Track::find($this->source->item_id ?? $this->source->track_id),
        };

        $this->rate = RateCalculator::getRate($item);
    }

    private function awardPartner()
    {
        if ($this->user->partner->invited->isEmpty()) {
            return null;
        }

        if (!$this->user->partner->invited->contains('user_id', '=', $this->buyer->id)) {
            return null;
        }

        $this->addDetails(FinancesEnv::USER_TYPE_PARTNER);
    }

    public function refund()
    {

    }

    public function setCreatedAtDate($date)
    {
        $this->detailedBalance->created_at = $date;
        $this->detailedBalance->updated_at = $date;
        $this->detailedBalance->save();

        return $this;
    }
}
