<?php

namespace App\Services;

use App\Models\Track;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use App\Models\SubscriptionHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DownloadService
{
    /**
     * @param Collection $tracksIds
     * @param Collection $videoEffectsIds
     * @param array $coefficients
     * @return Collection
     */
    public function getDownloadsByIds(
        Collection $tracksIds,
        Collection $videoEffectsIds,
        array $coefficients = [],
    ): Collection
    {
        $totalByMonths = SubscriptionHistory::select([
            DB::raw('sum(payment) as earnings'),
            DB::raw('DATE_FORMAT(created_at,"%Y-%m") as months')
        ])->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->first();

        $countDownloadsTrackByMonths = UserDownloads::whereIn('license_id', [12, 13])
            ->where('class', Track::class)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $countDownloadsVideoByMonths = UserDownloads::whereIn('license_id', [12, 13])
            ->where('class', VideoEffect::class)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $downloadOneTrack = $countDownloadsTrackByMonths ? $totalByMonths->earnings * (1 - $coefficients['fee'])
            * $coefficients['wmusic'] / $countDownloadsTrackByMonths : 0;
        $downloadOneVideoEffect = $countDownloadsVideoByMonths ? $totalByMonths->earnings * (1 - $coefficients['fee'])
            * $coefficients['wvideo'] / $countDownloadsVideoByMonths : 0;

        $downloadsTrack = UserDownloads::whereIn('license_id', [12, 13])
            ->whereIn('track_id', $tracksIds)
            ->where('class', Track::class)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->with(['downloadable'])
            ->get()->map(function ($item) use ($coefficients, $downloadOneTrack) {
                $award = $downloadOneTrack * ($item->downloadable->exclusive ? 0.5 : 0.4);
                $class = explode('\\', Track::class);
                return [
                    'date' => $item->created_at->timestamp,
                    'product_id' => $item->downloadable?->id,
                    'productName' => $item->downloadable?->name,
                    'productType' => end($class),
                    'rate' => $item->downloadable->exclusive ? 50 : 40,
                    'discount' => 0,
                    'earnings' => $award <= 0.3 ? 0.3 :
                        (float)number_format($award, 2, '.', ''),
                    'type' => $item->license?->type,
                    'payment_type' => $item->license?->payment_type,
                    'type_licence' => null,
                ];
            });

        $downloadsVideoEffects = UserDownloads::whereIn('license_id', [12, 13])
            ->whereIn('track_id', $videoEffectsIds)
            ->where('class', VideoEffect::class)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->get()->map(function ($item) use ($coefficients, $downloadOneVideoEffect) {
                $award = $downloadOneVideoEffect * ($item->downloadable->exclusive ? 0.5 : 0.4);
                $class = explode('\\', VideoEffect::class);
                return [
                    'date' => $item->created_at->timestamp,
                    'product_id' => $item->downloadable?->id,
                    'productName' => $item->downloadable?->name,
                    'productType' => end($class),
                    'rate' => $item->downloadable->exclusive ? 50 : 40,
                    'discount' => 0,
                    'earnings' => $award <= 0.3 ? 0.3 :
                        (float)number_format($award, 2, '.', ''),
                    'type' => $item->license?->type,
                    'payment_type' => $item->license?->payment_type,
                    'type_licence' => null,
                ];
            });

        return $downloadsTrack->concat($downloadsVideoEffects);
    }
}
