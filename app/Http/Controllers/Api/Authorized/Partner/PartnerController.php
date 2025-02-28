<?php

namespace App\Http\Controllers\Api\Authorized\Partner;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Libs\PartnerProgram;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerLinks;
use App\Http\Controllers\Api\ApiController;
use App\Models\Partner\PartnerEarningHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PartnerController extends ApiController
{
    public function generateLink(Partner $partner): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string',
        ]);

        $url = rtrim(request()->get('url', config('app.url')), '/');
        $name = request()->get('name');
        $hash = request()->get('hash');
        $hash = str_replace('?ref=', '', $hash);

        PartnerLinks::firstOrCreate([
            'partner_id' => $partner->id,
            'site_link' => $url,
            'hash' => $hash,
            'name' => $name,
        ]);

        return $this->success(['links' => $partner->refresh()->links]);
    }

    public function update(Partner $partner): JsonResponse
    {
        $pp_acc = request()->get('paypal_account');

        if (!$pp_acc) {
            return $this->success($partner->toArray());
        }

        $partner->paypal_account = $pp_acc;
        $partner->save();

        return $this->success($partner->refresh()->toArray());
    }

    public function deleteLink(Partner $partner, PartnerLinks $link): JsonResponse
    {
        $link->delete();

        return $this->success(
            array_merge(
                ['links' => $link->partner->links],
                $link->partner->toArray()
            )
        );
    }

    public function statistics(Partner $partner): JsonResponse
    {
        $linkId = request()->get('link_id');
        $timestampFrom = (int)request()->get('timestamp_from', Carbon::now()->startOfMonth()->timestamp);
        $timestampTo = (int)request()->get('timestamp_to', Carbon::now()->endOfDay()->timestamp);

        if (!$linkId) {
            $invitedCollection = $partner->invited;
        } else {
            $link = PartnerLinks::whereId($linkId)->first();
            $invitedCollection = $link->invited;
        }

        $carbonBetween = [
            Carbon::createFromTimestamp($timestampFrom)->startOfDay(),
            Carbon::createFromTimestamp($timestampTo)->addDay()->endOfDay()
        ];

        $invitedCollection->load('user');

        $userIds = $invitedCollection->whereBetween('user.created_at', $carbonBetween)
            ->pluck('user_id')
            ->values()
            ->toArray();
        $linkUsers = $invitedCollection->pluck('user_id')->values()->toArray();

        $earningHistory = PartnerEarningHistory::whereBetween('created_at', $carbonBetween)
            ->where('partner_id', $partner->id)
            ->whereIn('user_id', $linkUsers)
            ->get();

        $summary = [
            'referrals' => auth()->id() === 20009 ? 1000 : count($userIds),
            'transactions' => auth()->id() === 20009 ? 1500 : $earningHistory->where('source', PartnerProgram::EARNING_SOURCE_ORDER)->count(),
            'subscriptions' => auth()->id() === 20009 ? 500 : $earningHistory->where('source', PartnerProgram::EARNING_SOURCE_SUBSCRIPTION)->count(),
            'bonus' => auth()->id() === 20009 ? 1000000 : $earningHistory->sum('award'),
        ];

        $graph = [];
        $top = [];
        $map = [];

        $earningHistory->each(function (PartnerEarningHistory $earningHistory) use (&$graph, &$top, &$map) {
            $date = $earningHistory->created_at->startOfDay()->timestamp;

            if (!isset($graph[$date])) {
                $graph[$date] = [
                    'data' => [
                        'transactions' => 0,
                        'subscriptions' => 0,
                        'bonus' => 0,
                    ],
                ];
            }

            $country = $earningHistory->user->country_code;
            $bonus = $earningHistory->award;

            if (!isset($top[$country])) {
                $top[$country] = [
                    'country' => $country,
                    'transactions' => 0,
                    'subscriptions' => 0,
                    'bonus' => 0,
                ];
            }
            if (!isset($map[$country])) {
                $map[$country] = [
                    'bonus' => 0,
                ];
            }

            $isTransaction = $earningHistory->source === PartnerProgram::EARNING_SOURCE_ORDER;
            $isSubscription = $earningHistory->source === PartnerProgram::EARNING_SOURCE_SUBSCRIPTION;

            if ($isSubscription) {
                $graph[$date]['data']['subscriptions']++;
                $top[$country]['subscriptions']++;
            }

            if ($isTransaction) {
                $graph[$date]['data']['transactions']++;
                $top[$country]['transactions']++;
            }

            $graph[$date]['data']['bonus'] += $bonus;
            $top[$country]['bonus'] += $bonus;
            $map[$country]['bonus'] += $bonus;
        });

        $dateRange = CarbonPeriod::createFromArray($carbonBetween);

        foreach ($dateRange as $carbon) {
            $date = $carbon->timestamp;

            if (!isset($graph[$date])) {
                $graph[$date] = [
                    'data' => [
                        'transactions' => 0,
                        'subscriptions' => 0,
                        'bonus' => 0,
                    ],
                ];
            }
        }

        return $this->success([
            'between' => [Carbon::now()->endOfDay(), Carbon::now()->subDay()->endOfDay()],
            'summary' => $summary,
            'graph' => auth()->id() === 20009 ? [
                Carbon::now()->addDay()->timestamp => [
                    'us' => [
                        'transactions' => 1000,
                        'subscriptions' => 1500,
                        'bonus' => 500,
                    ],
                    'ru' => [
                        'transactions' => 900,
                        'subscriptions' => 1400,
                        'bonus' => 400,
                    ],
                    'uk' => [
                        'transactions' => 800,
                        'subscriptions' => 200,
                        'bonus' => 10,
                    ],
                ],
                Carbon::now()->addDay()->startOfDay()->timestamp => [
                    'us' => [
                        'transactions' => 1000,
                        'subscriptions' => 1500,
                        'bonus' => 500,
                    ],
                    'ru' => [
                        'transactions' => 800,
                        'subscriptions' => 200,
                        'bonus' => 10,
                    ],
                ],
                Carbon::now()->addDays(2)->startOfDay()->timestamp => [
                    'us' => [
                        'transactions' => 11200,
                        'subscriptions' => 124,
                        'bonus' => 3434,
                    ],
                    'ru' => [
                        'transactions' => 800,
                        'subscriptions' => 200,
                        'bonus' => 10,
                    ],
                ],
            ] : $graph,
            'top' => auth()->id() === 20009 ? [
                [
                    'country' => 'USA',
                    'transactions' => 1000,
                    'subscriptions' => 1500,
                    'bonus' => 500,
                ],
                [
                    'country' => 'USA',
                    'transactions' => 900,
                    'subscriptions' => 1400,
                    'bonus' => 400,
                ],
                [
                    'country' => 'Great Britain',
                    'transactions' => 800,
                    'subscriptions' => 200,
                    'bonus' => 10,
                ],
            ] : array_values($top),
            'map' => auth()->id() === 20009 ? [
                'us' => [
                    'bonus' => 123123123,
                ],
                'ru' => [
                    'bonus' => 7777,
                ],
                'gb' => [
                    'bonus' => 999,
                ],
            ] : $map,
        ]);
    }
}
