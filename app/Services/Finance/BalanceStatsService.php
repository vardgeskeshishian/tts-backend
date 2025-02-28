<?php


namespace App\Services\Finance;

use App\Constants\Env;
use App\Constants\FinancesEnv;
use App\Helpers\AuthorItems;
use App\Helpers\Finance\RateCalculator;
use App\Helpers\ItemRecognition;
use App\Models\Authors\Author;
use App\Models\Authors\AuthorProfile;
use App\Models\Finance\Balance;
use App\Models\Finance\DetailedBalance;
use App\Models\SubscriptionHistory;
use App\Models\Track;
use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BalanceStatsService
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
     * @var AuthorProfile
     */
    private $authorProfile;

    public function setUser(User $user)
    {
        $isAuthor = $user->isAuthor();
        $isPartner = $isAuthor ?: $user->isPartner();

        if (!$isPartner) {
            return [];
        }

        $this->is_author = $isAuthor;
        $this->author = $user instanceof Author ? $user : Author::find($user->id);
        $this->user = ($user instanceof Author) ? User::find($user->id) : $user;

        $this->balances = Balance::where([
            'user_id' => $this->user->id,
        ])->get();

        return $this;
    }


    /**
     * @param $profileId
     *
     * @return $this
     * @throws Exception
     */
    public function setProfile($profileId)
    {
        if ($profileId && !$this->author->profiles->contains('id', $profileId)) {
            throw new Exception("profile {$profileId} doesn't belong to current author");
        }

        $this->authorProfile = AuthorProfile::find($profileId);

        return $this;
    }

    public function getUserState()
    {
        return [
            'user' => $this->user,
            'status' => $this->is_author ? 'author' : 'partner',
            'linked_names' => $this->is_author ? $this->author->profiles->pluck('name')->implode(',') : null,
        ];
    }

    public function getCurrentBalance()
    {
        $activeBalances = $this->balances
            ->where('status', 'awaiting');

        return [
            'author_balance' => $activeBalances->sum('author_balance') ?? 0,
            'partner_balance' => $activeBalances->sum('partner_balance') ?? 0,
            'total_balance' => $activeBalances->sum(function (Balance $balance) {
                return $balance->getTotalBalance();
            }),
        ];
    }

    public function getPortfolioHistory($dateStart = null, $dateEnd = null)
    {
        if ($this->author->profiles->isEmpty()) {
            return [
                'earnings' => [],
                'graph' => [],
                'map' => [],
                'top' => [],
            ];
        }

        /**
         * @var $dateStart Carbon
         * @var $dateEnd Carbon
         */
        [$dateStart, $dateEnd] = $this->getStartEndDates($dateStart, $dateEnd);

        $profileIds = $this->getProfilesIdsFilter();

        $items = (new AuthorItems)->getAuthorItems($profileIds);

        $summary = DetailedBalance::whereBetween('created_at', [$dateStart, $dateEnd])
            ->where(fn($q) => $q
                ->where(fn($q) => $q->where('item_type', '!=', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->whereIn('track_id', $items->trackIds))
                ->orWhere(fn($q) => $q->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->whereIn('track_id', $items->templateIds))
            )
            ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
            ->get();

        $earnings = $summary->map(function (DetailedBalance $summary) {
            $frontDate = $summary->created_at->format('d.m.Y');

            return collect([
                'frontDate' => $frontDate,
                'transactions' => [
                    'date' => $frontDate,
                    'count' => $summary->source_type === FinancesEnv::SOURCE_TYPE_ORDER_ITEM ? 1 : null,
                    'bonus' => $summary->source_type === FinancesEnv::SOURCE_TYPE_ORDER_ITEM ? $summary->award : null,
                ],
                'subscriptions' => [
                    'date' => $frontDate,
                    'count' => $summary->source_type === FinancesEnv::SOURCE_TYPE_A_DOWNLOAD ? 1 : null,
                    'bonus' => $summary->source_type === FinancesEnv::SOURCE_TYPE_A_DOWNLOAD ? $summary->award : null,
                ],
            ]);
        })->groupBy(function (Collection $collection) {
            return $collection['frontDate'];
        })->map(function (Collection $collection, $date) {
            $transactionsCount = $collection->sum('transactions.count');
            $transactionsBonus = $collection->sum('transactions.bonus');
            $subCount = $collection->sum('subscriptions.count');
            $subBonus = $collection->sum('subscriptions.bonus');

            return [
                'transactions' => [
                    'date' => $date,
                    'count' => $transactionsCount,
                    'bonus' => $transactionsBonus > 0 ? $transactionsBonus : null,
                ],
                'subscriptions' => [
                    'date' => $date,
                    'count' => $subCount,
                    'bonus' => $subBonus > 0 ? $subBonus : null,
                ]
            ];
        })->sortBy('transactions.date')->values();

        $mainData = [
            'earnings' => $earnings,
            'top-countries' => $summary->groupBy(function (DetailedBalance $balance) {
                return $balance->country_code;
            })->mapWithKeys(function (Collection $collection, $key) {
                $salesCollection = $collection->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM);

                $salesCount = $salesCollection->count();
                $salesAward = $salesCollection->sum('award');
                $subs = $collection->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)->count();

                return [
                    $key => [
                        'transactions' => $salesCount,
                        'subscriptions' => $subs,
                        'bonus' => $salesAward,
                    ]
                ];
            }),
        ];

        $graph = $earnings->map(function ($collection) {
            $collection['transactions']['date'] = Carbon::parse($collection['transactions']['date'])->timestamp;
            $collection['subscriptions']['date'] = Carbon::parse($collection['subscriptions']['date'])->timestamp;
            return [
                'data' => $collection
            ];
        })->values();

        $top = $mainData['top-countries']->map(function ($collection, $country) {
            return array_merge($collection, ['country' => $country]);
        })->values();

        return [
            'earnings' => $mainData['earnings'],
            'graph' => $graph,
            'map' => $mainData['top-countries'],
            'top' => $top,
        ];
    }

    /**
     * @param $dateStart
     * @param $dateEnd
     * @return array[Carbon]
     */
    private function getStartEndDates($dateStart, $dateEnd)
    {
        if ($dateStart && is_numeric($dateStart)) {
            $dateStart = Carbon::createFromTimestamp($dateStart);
        }

        if ($dateEnd && is_numeric($dateEnd)) {
            $dateEnd = Carbon::createFromTimestamp($dateEnd);
        }

        $dateStart = $dateStart ? Carbon::parse($dateStart) : Carbon::now()->startOfMonth();
        $dateEnd = $dateEnd ? Carbon::parse($dateEnd) : Carbon::now()->endOfDay();

        $dateStart = $dateStart->addHours(3)->startOfDay();
        $dateEnd = $dateEnd->addHours(3)->endOfDay();

        return [$dateStart, $dateEnd];
    }

    private function getProfilesIdsFilter()
    {
        if ($this->authorProfile) {
            return collect($this->authorProfile->id);
        }

        return $this->author->profiles->pluck('id');
    }

    public function getStatements($dateStart = null, $dateEnd = null)
    {
        if ($this->author->profiles->isEmpty()) {
            return [
                'earnings' => [],
                'payouts' => [],
            ];
        }

        [$dateStart, $dateEnd] = $this->getStartEndDates($dateStart, $dateEnd);

        $profileIds = $this->getProfilesIdsFilter();

        $items = (new AuthorItems)->getAuthorItems($profileIds);

        $earnings = DetailedBalance::forAuthor()->onlySales()
            ->whereIn('balance_id', $this->balances->pluck('id'))
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->get()
            ->map(function (DetailedBalance $balance) {
                return [
                    'date' => $balance->created_at->format('d.m.Y'),
                    'track' => $balance->getItemAttribute(),
                    'details' => sprintf(
                        "%s (%s)",
                        optional($balance->item)->full_name,
                        optional($balance->license)->type
                    ),
                    'rate' => $balance->rate,
                    'discount' => $balance->discount,
                    'earnings' => $balance->award,
                    'balance' => $balance,
                ];
            });

        $payouts = $this->balances;

        $subscriptionBalances = DetailedBalance::forAuthor()
            ->onlyDownloads()
            ->with('track', 'videoEffect')
            ->where('created_at', '>=', $dateStart)
            ->where('created_at', '<=', $dateEnd)
            ->where(fn($q) => $q
                ->where(fn($q) => $q->where('item_type', '!=', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->whereIn('track_id', $items->trackIds))
                ->orWhere(fn($q) => $q->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->whereIn('track_id', $items->templateIds))
            )
            ->whereNotNull('license_id')
            ->get();

        /**
         * @var $subBalance DetailedBalance
         */
        foreach ($subscriptionBalances as $subBalance) {
            $earnings[] = [
                'date' => $subBalance->created_at->format('d.m.Y'),
                'track' => $subBalance->item,
                'details' => sprintf("%s (subscription)", optional($subBalance->item)->full_name),
                'rate' => null,
                'discount' => null,
                'earnings' => $subBalance->award,
            ];
        }

        return [
            'itemIds' => $items->itemIds,
            'earnings' => $earnings->sortBy('date')->values(),
            'payouts' => $payouts
                ->filter(function (Balance $balance) {
                    return $balance->date !== FinanceService::getFinanceDate(Carbon::now());
                })
                ->mapWithKeys(function (Balance $balance) {
                    return [
                        $balance->date =>
                            [
                                'date' => $balance->date,
                                'type' => $balance->payment_type,
                                'email' => $balance->payment_email,
                                'monthly_total' => $balance->getTotalBalance(),
                                'status' => $balance->status,
                                'updated_at' => $balance->confirmed_at,
                            ],
                    ];
                }),
        ];
    }

    public function calculateGeneralBalanceInformation()
    {
        $balances = $this->balances->loadMissing('details');

        $profiles = '';
        $submissions_count = 0;
        $isEmptyGeneralBalance = false;

        if ($this->is_author) {
            $profiles = $this->author->profiles ? $this->author->profiles->pluck('name')->all() : [];
            $submissions_count = $this->author->submissions_count;

            if ($this->author->profiles->isEmpty()) {
                $isEmptyGeneralBalance = true;
            } else {
                $profileIds = $this->getProfilesIdsFilter();

                $items = (new AuthorItems)->getAuthorItems($profileIds);

                $submissions_count += $items->count;
            }
        }

        if ($balances->isEmpty()) {
            $isEmptyGeneralBalance = true;
        }

        if ($isEmptyGeneralBalance) {
            return [
                'type' => $this->is_author ? 'author' : 'partner',
                'email' => $this->user->email,
                'linked_authors' => $profiles,
                'tracks' => $submissions_count,
                'sales' => 0,
                'sub_downloads' => 0,
                'ref_link' => $this->user->partner ? $this->user->partner->links->pluck('hash')->unique()->implode(',') : '',
                'invited' => $this->user->partner->invited_count ?? null,
                'cb' => 0,
                'trb' => 0,
                'tae' => 0,
                'tse' => 0,
                'total' => $balances->sum('balance'),
            ];
        }

        $currentBalance = $balances->where('date', FinanceService::getFinanceDate(Carbon::now()))->first();

        $profileIds = $this->getProfilesIdsFilter();

        $items = (new AuthorItems)->getAuthorItems($profileIds);

        return [
            'type' => 'author',
            'email' => $this->user->email,
            'linked_authors' => $profiles,
            'tracks' => $submissions_count,
            'sales' => $balances->sum(function (Balance $balance) {
                return $balance->details
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->count();
            }),
            'sub_downloads' =>
                DetailedBalance::where(fn($q) => $q
                    ->where(fn($q) => $q->where('item_type', '!=', Env::ITEM_TYPE_VIDEO_EFFECTS)
                        ->whereIn('track_id', $items->trackIds))
                    ->orWhere(fn($q) => $q->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
                        ->whereIn('track_id', $items->templateIds))
                )
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->count(),
            'ref_link' => $this->user->partner->links->pluck('hash')->unique()->implode(','),
            'invited' => $this->user->partner->invited_count,
            'cb' => $currentBalance ? $currentBalance->getTotalBalance() : 0,
            'trb' => $balances->sum('partner_balance'),
            'tae' => $balances->sum(function (Balance $balance) {
                return $balance->details
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->sum('award');
            }),
            'tse' => $balances->sum(function (Balance $balance) {
                return $balance->details
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->sum('award');
            }),
            'total' => $balances->sum(function (Balance $balance) {
                return $balance->getTotalBalance();
            }),
        ];
    }

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

            yield [
                'date' => $balance->date,
                'balances' => [$balance->id],
                'email' => $this->user->email,
                'payment-type' => $balance->payment_type,
                'payment-email' => $balance->payment_email,
                'mrb' => $balance->partner_balance,
                'mae' => $authorDetails
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                    ->sum('award'),
                'mse' => $authorDetails
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->sum('award'),
                'm-total' => $balance->getTotalBalance(),
                'payment-status' => $balance->status,
            ];
        }
    }

    public function getReferralEarnings()
    {
        $information = [];

        foreach ($this->balances as $balance) {
            $balance->details->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                ->where('user_type', FinancesEnv::USER_TYPE_PARTNER)
                ->each(function (DetailedBalance $detailedBalance) use (&$information) {
                    $information[$detailedBalance->id] = [
                        'buyer_id' => $detailedBalance->buyer_id,
                        'award' => $detailedBalance->award,
                        'source' => $detailedBalance->source_type,
                        'created_at' => $detailedBalance->created_at,
                    ];
                });
        }

        return $information;
    }

    public function getSubmissionsStats()
    {
        if (!$this->is_author) {
            return collect();
        }

        $profilesIdsFilter = $this->getProfilesIdsFilter();

        if (!$profilesIdsFilter || $profilesIdsFilter->isEmpty()) {
            return collect();
        }

        $items = (new AuthorItems)->getAuthorItems($profilesIdsFilter);

        if ($items->count === 0) {
            return collect();
        }

        $balanceSummary = DetailedBalance::forAuthor()
            ->with('track', 'videoEffect')
            ->where(fn($q) => $q
                ->where(fn($q) => $q->where('item_type', '!=', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->whereIn('track_id', $items->trackIds))
                ->orWhere(fn($q) => $q->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->whereIn('track_id', $items->templateIds))
            )
            ->get()
            ->groupBy(['item_type', 'track_id']);

        $information = [];

        /**
         * @var Track|VideoEffect $item
         */
        foreach ($items->items as $item) {
            $information[ItemRecognition::getDistinctItemIdByModel($item)] = [
                'track' => $item,
                'sales' => 0,
                'sub_downloads' => 0,
                'rate' => RateCalculator::getRate($item),
                'total_earnings' => 0,
                'sub_earnings' => 0,
                'total' => 0,
            ];
        }

        /**
         * @var DetailedBalance $summary
         */
        foreach ($balanceSummary as $itemType => $balances) {
            foreach ($balances as $itemId => $summary) {
                $distinctItemId = ItemRecognition::getDistinctItemIdByType($itemType, $itemId);

                $information[$distinctItemId] = [
                    'track' => $summary->first()->getItemAttribute(),
                    'sales' => $summary
                        ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                        ->count(),
                    'sub_downloads' => $this->getDownloadsByTrack($itemType, $itemId),
                    'rate' => $summary->first()->rate,
                    'total_earnings' => $summary
                        ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                        ->sum('award'),
                    'sub_earnings' => $summary->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)->sum('award'),
                ];

                $information[$distinctItemId]['track']['has_content_id_2'] = $summary->first()->getItemAttribute()?->has_content_id;

                $information[$distinctItemId]['total'] = $information[$distinctItemId]['total_earnings']
                    + $information[$distinctItemId]['sub_earnings'];
            }
        }

        return collect($information)->sortBy('track.created_at');
    }

    public function getDownloadsByTrack($itemType, $itemId): int
    {
        return DetailedBalance::forAuthor()->onlyDownloads()
            ->where('item_type', $itemType)
            ->where('track_id', $itemId)->count();
    }

    public function getAuthorEarnings()
    {
        if (!$this->is_author) {
            return collect();
        }

        $information = [];

        $statementService = resolve(StatementService::class);

        $items = (new AuthorItems)->getAuthorItems($this->getProfilesIdsFilter());

        foreach ($this->balances as $balance) {
            $sales = $balance->details
                ->where('source_type', FinancesEnv::SOURCE_TYPE_ORDER_ITEM)
                ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                ->whereIn('track_id', $items->itemIds);

            $downloads = $balance->details
                ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                ->whereIn('track_id', $items->itemIds);

            $authorShare = $statementService->getAuthorShareForDate($this->author, $balance->date);

            $info = [
                'sales' => $sales->count(),
                'earnings' => $sales->sum('award'),
                'as' => $authorShare,
                'sub_downloads' => $downloads->count(),
                'sub_earnings' => $downloads->sum('award'),
                'payout_status' => $balance->status,
                'payment_type' => $balance->payment_type,
                'payment_email' => $balance->payment_email,
                'trb' => $balance->partner_balance ?? 0,
            ];

            $info['at'] = optional($balance->details->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)->first())->award ?? 0;
            $info['total'] = $balance->author_balance + $info['trb'];

            $information[$balance->date] = $info;
        }

        return collect($information);
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

        $previousSubEarningsAudio = $previousSubEarnings * 0.86 * 0.9; // todo: 0.9 should be editable from admin
        $previousSubEarningsVideo = $previousSubEarnings * 0.86 * 0.1; // todo: 0.1 should be editable from admin

        return [
            'audio' => [
                'exc' => ($previousSubEarningsAudio / $subAudio) * 0.5,
                'non-exc' => ($previousSubEarningsAudio / $subAudio) * 0.4
            ],
            'video' => [
                'exc' => ($previousSubEarningsVideo / $subVideo) * 0.5,
                'non-exc' => ($previousSubEarningsVideo / $subVideo) * 0.4
            ],
        ];
    }

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

    /**
     * @param User $user
     * @return array
     */
    public function getPayouts(User $user): array
    {
        if (!$user->isAuthor()) {
            return [];
        }

        $payouts = Balance::where([
            'user_id' => $user->id,
        ])->orderBy('date')->get()->map(function ($payout) {
            return [
                'id' => $payout->id,
                'month' => Carbon::create($payout->date)->subMonth()->timestamp,
                'type' => $payout->payment_type,
                'payment_email' => $payout->payment_email,
                'monthly_total' => $payout->author_balance,
                'status' => $payout->status,
                'update' => Carbon::create($payout->updated_at)->timestamp,
            ];
        });

        return [
            'payouts' => $payouts,
            'total' => $payouts->sum('monthly_total')
        ];
    }
}
