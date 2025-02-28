<?php

namespace App\Services;

use App\Models\Finance\Balance;
use App\Models\Finance\DetailedBalance;
use App\Models\OrderItem;
use App\Models\PayoutCoefficient;
use App\Models\SubscriptionHistory;
use App\Models\Track;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BalanceService
{
    /**
     * @return void
     */
    public function calculate(): void
    {
        $coefficients = PayoutCoefficient::pluck('value', 'name')->toArray();

        $prevMonth = Carbon::now()->day == 1 ? Carbon::now()->subDay()->startOfMonth() : Carbon::now()->startOfMonth();
        $nextMonth = Carbon::now()->day == 1 ? Carbon::now() : Carbon::now()->addMonth();

        $users = User::has('authors')->with('authors')->get();

        $sumSubscriptionsCurrentMonth = SubscriptionHistory::select([
            DB::raw('sum(payment) as earnings'),
            DB::raw('DATE_FORMAT(created_at,"%Y-%m") as months')
        ])->whereDate('created_at', '>=', $prevMonth)
            ->first();

        $countDonwloadsTrack = UserDownloads::whereIn('license_id', [12, 13])
            ->where('class', Track::class)
            ->whereDate('created_at', '>=', $prevMonth)
            ->count();

        $countDownloadsVideoEffects = UserDownloads::whereIn('license_id', [12, 13])
            ->where('class', VideoEffect::class)
            ->whereDate('created_at', '>=', $prevMonth)
            ->count();

        $downloadOneTrack = $countDonwloadsTrack ? $sumSubscriptionsCurrentMonth->earnings * (1 - $coefficients['fee'])
            * $coefficients['wmusic'] / $countDonwloadsTrack : 0;
        $downloadOneVideoEffect = $countDownloadsVideoEffects ? $sumSubscriptionsCurrentMonth->earnings * (1 - $coefficients['fee'])
            * $coefficients['wvideo'] / $countDownloadsVideoEffects : 0;

        $fullEarnings = $sumSubscriptionsCurrentMonth->earnings;

        foreach ($users as $user)
        {
            $author_balance = 0;

            $tracksIds = Track::whereIn('author_profile_id', $user->authors->pluck('id')->toArray())
                ->pluck('id');

            $videoEffectsIds = VideoEffect::where('author_profile_id', $user->authors->pluck('id')->toArray())
                ->pluck('id');

            $downloadsTrack = UserDownloads::whereIn('license_id', [12, 13])
                ->whereIn('track_id', $tracksIds)
                ->where('class', Track::class)
                ->whereDate('created_at', '>=', $prevMonth)
                ->with(['downloadable'])
                ->get()->map(function ($item) use ($coefficients, $downloadOneTrack) {
                    $award = $downloadOneTrack * ($item->downloadable->exclusive ? 0.5 : 0.4);
                    return [
                        'source_id' => $item->id,
                        'source_type' => UserDownloads::class,
                        'percentage' => $coefficients['wmusic'],
                        'award' => (float)number_format(max($award, 0.3), 2, '.', ''),
                        'buyer_id' => $item->user_id,
                        'original_price' => 0.0,
                        'license_id' => $item->license_id,
                        'track_id' => $item->track_id,
                        'rate' => $coefficients['wmusic'],
                        'item_type' => $item->class,
                        'user_type' => 'author',
                    ];
                });

            $downloadsVideoEffects = UserDownloads::whereIn('license_id', [12, 13])
                ->whereIn('track_id', $videoEffectsIds)
                ->where('class', VideoEffect::class)
                ->whereDate('created_at', '>=', $prevMonth)
                ->get()->map(function ($item) use ($coefficients, $downloadOneVideoEffect) {
                    $award = $downloadOneVideoEffect * ($item->downloadable->exclusive ? 0.5 : 0.4);
                    return [
                        'source_id' => $item->id,
                        'source_type' => UserDownloads::class,
                        'percentage' => $coefficients['wvideo'],
                        'award' => (float)number_format(max($award, 0.3), 2, '.', ''),
                        'buyer_id' => $item->user_id,
                        'original_price' => 0.0,
                        'license_id' => $item->license_id,
                        'track_id' => $item->track_id,
                        'rate' => $coefficients['wvideo'],
                        'item_type' => $item->class,
                        'user_type' => 'author'
                    ];
                });

            $subs = $downloadsTrack->sum('award')
                + $downloadsVideoEffects->sum('award');

            $author_balance += $subs;

            $orderItem = OrderItem::where(function ($query) use ($tracksIds, $videoEffectsIds) {
                $query->where(function ($query) use ($tracksIds) {
                    $query->whereIn('track_id', $tracksIds)
                        ->where('item_type', Track::class);
                })->orWhere(function ($query) use ($videoEffectsIds) {
                    $query->whereIn('track_id', $videoEffectsIds)
                        ->where('item_type', VideoEffect::class);
                });
            })->whereDate('created_at', '>=', $prevMonth)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'finished');
                })->with(['orderItemable', 'order'])->get()
                ->map(function ($item) use ($coefficients, &$fullEarnings) {
                    $percentage = $item->orderItemable->exclusive ? $coefficients['wex'] : $coefficients['wnoex'];
                    $fullEarnings += $item->price;
                    return [
                        'source_id' => $item->id,
                        'source_type' => OrderItem::class,
                        'percentage' => $percentage,
                        'award' => (float)number_format($item->price * $percentage * (1 - $coefficients['fee']), 2, '.', ''),
                        'buyer_id' => $item->order?->user_id,
                        'original_price' => $item->price,
                        'license_id' => $item->license_id,
                        'track_id' => $item->track_id,
                        'rate' => $percentage,
                        'item_type' => $item->item_type,
                        'user_type' => 'author'
                    ];
                });

            $single = $orderItem->sum('award');

            $author_balance += $single;

            $prevPrevMonth = Carbon::now()->subMonths(2);

            $prevBalance = Balance::where('user_id', $user->id)
                ->where('date', $prevPrevMonth->year.'-'.$prevPrevMonth->month)
                ->where('status', 'awaiting')->first();

            $unpaid = 0;
            if ($prevBalance)
                $unpaid = $prevBalance->unpaid + $prevBalance->author_balance;

            $balance = Balance::updateOrCreate([
                'user_id' => $user->id,
                'date' => $nextMonth->year.'-'.$nextMonth->month,
            ], [
                'unpaid' => $unpaid,
                'status' => 'awaiting',
                'payment_email' => $user->payout_email,
                'author_balance' => $author_balance,
                'single' => $single,
                'subs' => $subs,
                'audio_downloads' => $downloadsTrack->count(),
                'video_downloads' => $downloadsVideoEffects->count()
            ]);

            $detailedBalanceUnion = $downloadsTrack->concat($downloadsVideoEffects)
                ->concat($orderItem)->toArray();
            foreach ($detailedBalanceUnion as $key => $item)
                $detailedBalanceUnion[$key]['balance_id'] = $balance->id;

            DetailedBalance::upsert($detailedBalanceUnion,
                ['balance_id', 'source_id', 'source_type', 'user_type'],
                ['percentage', 'award', 'buyer_id', 'original_price', 'license_id',
                    'track_id', 'rate', 'item_type']
            );
        }

        PayoutCoefficient::where('name', 'day_prev_calculate')
            ->update([
                'value' => 0
            ]);

        PayoutCoefficient::where('name', 'full_earnings')
            ->update([
                'value' => $fullEarnings
            ]);

        $classicEarnings = SubscriptionHistory::select([
            DB::raw('sum(payment) as earnings'),
            DB::raw('DATE_FORMAT(created_at,"%Y-%m") as months')
        ])->whereNull('transaction_id')
            ->whereDate('created_at', '>=', $prevMonth)
            ->first();

        PayoutCoefficient::where('name', 'сlassic_earnings')
            ->update([
                'value' => $classicEarnings->earnings
            ]);

        $billingEarnings = SubscriptionHistory::select([
            DB::raw('sum(payment) as earnings'),
            DB::raw('DATE_FORMAT(created_at,"%Y-%m") as months')
        ])->whereNotNull('transaction_id')
            ->whereDate('created_at', '>=', $prevMonth)
            ->first();

        PayoutCoefficient::where('name', 'billing_earnings')
            ->update([
                'value' => $billingEarnings->earnings
            ]);

        PayoutCoefficient::where('name', 'total_downloads')
            ->update([
                'value' => $countDonwloadsTrack + $countDownloadsVideoEffects
            ]);

        PayoutCoefficient::where('name', 'audio_downloads')
            ->update([
                'value' => $countDonwloadsTrack
            ]);

        PayoutCoefficient::where('name', 'video_downloads')
            ->update([
                'value' => $countDownloadsVideoEffects
            ]);

        PayoutCoefficient::where('name', 'cost_per_audio')
            ->update([
                'value' => $downloadOneTrack
            ]);

        PayoutCoefficient::where('name', 'cost_per_video')
            ->update([
                'value' => $downloadOneVideoEffect
            ]);

        PayoutCoefficient::where('name', 'prev_fee')
            ->update([
                'value' => $coefficients['fee']
            ]);

        PayoutCoefficient::where('name', 'prev_wmusic')
            ->update([
                'value' => $coefficients['wmusic']
            ]);

        PayoutCoefficient::where('name', 'prev_wvideo')
            ->update([
                'value' => $coefficients['wvideo']
            ]);

        PayoutCoefficient::where('name', 'prev_wex')
            ->update([
                'value' => $coefficients['wex']
            ]);

        PayoutCoefficient::where('name', 'prev_wnoex')
            ->update([
                'value' => $coefficients['wnoex']
            ]);
    }

    /**
     * @param string $user_id
     * @return Collection
     */
    public function getEarnings(string $user_id): Collection
    {
        return DetailedBalance::whereHas('balance', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->orderByDesc('created_at')->get()->map(function ($item) {
            $class = explode('\\', $item->item_type);
            return [
                'date' => Carbon::parse($item->balance->date)->subMonth()->timestamp
                < Carbon::parse('2025-01-01 00:00:00')->timestamp
                    ? Carbon::parse($item->balance->date)->subMonth()->timestamp
                    : $item->source?->created_at->timestamp,
                'product_id' => $item->track_id,
                'productName' => $item->detailedBalancesable?->name,
                'productType' => end($class),
                'rate' => $item->rate ? 50 : 40,
                'discount' => 0,
                'earnings' => $item->award <= 0.3 ? 0.3 :
                    (float)number_format($item->award, 2, '.', ''),
                'type' => $item->license?->type,
                'payment_type' => $item->license?->payment_type,
                'type_licence' => null,
            ];
        });
    }
}