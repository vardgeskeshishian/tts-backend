<?php

namespace App\Services\Finance;

use Carbon\Carbon;
use App\Models\User;
use App\Constants\Env;
use App\Constants\FinancesEnv;
use App\Models\Finance\Balance;
use App\Models\PayoutCoefficient;
use App\Models\SubscriptionHistory;
use App\Models\Authors\AuthorProfile;
use App\Models\Finance\DetailedBalance;

class AuthorProfilesBalanceStatsService
{
    /**
     * @var User
     */
    private User $user;

    private bool $is_author = false;

    /**
     * @var Author
     */
    private $author;

    /**
     * @var Balance[]|\Illuminate\Database\Eloquent\Collection
     */
    private $balances;

    /**
     * Set user author
     *
     * @param object $user
     * @return object
     */
    public function setUser(User $user)
    {
        $isAuthor = $user->isAuthor();
        $isPartner = $isAuthor ?: $user->isPartner();

        if (!$isPartner) {
            return [];
        }

        $this->is_author = $isAuthor;
        $this->author = $user instanceof AuthorProfile ? $user : AuthorProfile::find($user->id);
        $this->user = ($user instanceof AuthorProfile) ? User::find($user->id) : $user;

        $this->balances = Balance::where([
            'user_id' => $this->user->id,
        ])->get();


        return $this;
    }

    /**
     * Calculate payout information by author
     *
     */
    public function calculatePayoutInformation()
    {
        $balances = $this->balances
            ->where('date', '<=', FinanceService::getFinanceDate(Carbon::now()->previous('month')))
            ->where('status', 'awaiting')
            ->loadMissing('details');

        if ($balances->isEmpty()) {
            return [];
        }

        foreach ($balances as $balance) {
            $authorDetails = $balance->details->where('user_type', FinancesEnv::USER_TYPE_AUTHOR);
            $balanceId = $balance->id;

            $allBalances = $this->balances
                ->where('status', 'awaiting')
                ->where('user_id', $this->user->id)
                ->loadMissing('details');

            $alltimeTotal = 0;

            foreach ($allBalances as $monthBalance) {
                $alltimeTotal += $monthBalance->getTotalBalance();
            }

            yield [
                'date'           => $balance->date,
                'balances'       => $balanceId,
                'email'          => $this->user->email,
                'payment-type'   => $balance->payment_type,
                'payment-email'  => $balance->payment_email,
                'mrb'            => $balance->partner_balance,
                'mae'            => $authorDetails
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                    ->sum('award'),
                'mse'            => $authorDetails
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->sum('award'),
                'm-total'        => $balance->getTotalBalance(),
                'alltime-total'  => $alltimeTotal,
                'payment-status' => $balance->status,
                'user-id'      => $this->user->id
            ];
        }
    }

    /**
     * Get coefficient's name 
     *
     * @param string $name
     * @return float|null
     */
    public function getCoefficientValue($name)
    {
        $coefficient = PayoutCoefficient::where('name', $name)->first();
        return $coefficient ? $coefficient->value : null;
    }

    /**
     * Absolute subscription award - used at the end of current month to get real subscription award
     *
     * @param $currentDate
     * @return array
     */
    public function calculateAbsoluteSubscriptionAward($currentDate)
    {
        if (Carbon::parse($currentDate)->lt(Carbon::parse('2019-11-05'))) {
            return [0, 0];
        }

        [$subAudio, $subVideo] = $this->getSubDownloadsForDate(Carbon::parse($currentDate));

        $previousSubEarnings = $this->calculateTotalMoneyFromSubscriptionForDate($currentDate);

        $fee = $this->getCoefficientValue('fee');
        $wmusic = $this->getCoefficientValue('wmusic');
        $wvideo = $this->getCoefficientValue('wvideo');
        $wex = $this->getCoefficientValue('wex');
        $wnoex = $this->getCoefficientValue('wnoex');

        $previousSubEarningsAudio = $previousSubEarnings * (1 - $fee) * $wmusic;
        $previousSubEarningsVideo = $previousSubEarnings * (1 - $fee) * $wvideo;

        return [
            'audio' => [
                'exc' => ($previousSubEarningsAudio > 0) ? ($previousSubEarningsAudio / $subAudio) * $wex : $previousSubEarningsAudio,
                'non-exc' => ($previousSubEarningsAudio > 0) ? ($previousSubEarningsAudio / $subAudio) * $wnoex : $previousSubEarningsAudio
            ],
            'video' => [
                'exc' => ($previousSubEarningsVideo > 0) ? ($previousSubEarningsVideo / $subVideo) * $wex : $previousSubEarningsVideo,
                'non-exc' => ($previousSubEarningsVideo > 0) ? ($previousSubEarningsVideo / $subVideo) * $wnoex : $previousSubEarningsVideo
            ],
        ];
    }

    /**
     * Getting downloads by date period
     *
     * @param $date
     * @return array
     */
    public function getSubDownloadsForDate($date): array
    {
        $between = [Carbon::parse($date)->startOfMonth()->startOfDay(), Carbon::parse($date)->endOfMonth()->endOfDay()];

        $audioCount = DetailedBalance::forAuthor()->onlyDownloads()
            ->whereBetween('created_at', $between)
            ->where('item_type', Env::ITEM_TYPE_TRACKS)
            ->count();

        $videoCount = DetailedBalance::forAuthor()->onlyDownloads()
            ->whereBetween('created_at', $between)
            ->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
            ->count();

        return [$audioCount, $videoCount];
    }

    /**
     * Calculate summ from subscriptions by date period
     *
     * @param $date
     * @return mixed
     */
    public function calculateTotalMoneyFromSubscriptionForDate($date): int
    {
        $start = Carbon::parse($date)->startOfMonth()->startOfDay();
        $end = Carbon::parse($date)->endOfMonth()->endOfDay();

        $histories = SubscriptionHistory::select(['id', 'payment', 'vat', 'subscription_id', 'user_id'])
            ->whereBetween('date', [$start, $end])
            ->where('payment', '>', 0)
            ->where('type', '!=', SubscriptionHistory::TYPE_REFUND)
            ->whereHas('user', function ($q) {
                $q->where('role', '!=', 'admin');
            })->get();

        $sum = 0;

        $processed = [];

        foreach ($histories as $history) {
            $processedKey = sprintf(
                "%d.%s",
                $history->subscription_id,
                $history->type ?? SubscriptionHistory::TYPE_SUCCEEDED
            );

            if (isset($processed[$processedKey])) {
                continue;
            }

            $sum += ($history->payment - $history->vat);

            $processed[$processedKey] = true;
        }

        return $sum;
    }
}
