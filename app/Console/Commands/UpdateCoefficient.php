<?php

namespace App\Console\Commands;

use App\Models\Coefficient;
use App\Models\SFX\SFXCoefficient;
use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\TracksCoefficient;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoCoefficient;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class UpdateCoefficient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:coefficients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update coefficients content';

    public function handle()
    {
        $coefficients = Cache::remember('search:coefficients', Carbon::now()->addSeconds(5), function () {
            return Coefficient::select(['short_name', 'coefficient'])
                ->get()
                ->mapWithKeys(fn($i) => [$i->short_name => $i->coefficient]);
        });

        $tracks = Track::withCount(['userDownloads as user_downloads_free_count' => function($query) use ($coefficients) {
            $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->where('license_id', 5)
                ->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                });
        }])->withCount(['userDownloads as user_downloads_subs_count' => function($query) use ($coefficients) {
            $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->whereIn('license_id', [12, 13])
                ->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                });
        }])->withCount('userDownloads')->get();

        $countDownloadsTranding = UserDownloads::where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
            ->where('class', Track::class)->where(function($query) {
                $query->orWhereNotNull('billing_product_id')
                    ->orWhere(function ($query) {
                        $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                    });
            })->whereHas('user', function ($query) {
                $query->where('email', 'not like', 'paulcarvine%')
                    ->where('email', 'not like', 'x-guitar%')
                    ->where('email', 'not like', 'aleksnc%')
                    ->where('email', 'not like', '45rock%')
                    ->where('email', 'not like', 'domosy%')
                    ->where('email', 'not like', 'pavelyu%')
                    ->where('email', 'not like', 'tdostu%')
                    ->where('email', 'not like', 'notbeforeant%')
                    ->where('email', 'not like', 'lobanov%');
            })->count();

        foreach ($tracks as $track)
        {
            $countDownloadsFree = $track->user_downloads_free_count;
            $countDownloadsSubs = $track->user_downloads_subs_count;
            $trend_free = $countDownloadsFree > 0 ? min($countDownloadsFree / $countDownloadsTranding * 100, 1) : 0;
            $trend_subs = $countDownloadsSubs > 0 ? min($countDownloadsSubs / $countDownloadsTranding * 100, 1) : 0;

            $result = $trend_free * $coefficients['free_coefficient'] +
                $trend_subs * $coefficients['subs_coefficient'];
            TracksCoefficient::updateOrCreate([
                'track_id' => $track->id,
            ], [
                'trending' => $result,
                'created_at' => $track->created_at,
                'downloads' => $track->user_downloads_count
            ]);
        }






        $videoEffects = VideoEffect::withCount(['userDownloads as user_downloads_free_count' => function($query) use ($coefficients) {
            $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->where('license_id', 5)
                ->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                });
        }])->withCount(['userDownloads as user_downloads_subs_count' => function($query) use ($coefficients) {
            $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->whereIn('license_id', [12, 13])
                ->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                });
        }])->withCount('userDownloads')->get();

        $countDownloadsTranding = UserDownloads::where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand_video']))
            ->where('class', VideoEffect::class)->where(function($query) {
                $query->orWhereNotNull('billing_product_id')
                    ->orWhere(function ($query) {
                        $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                    });
            })->whereHas('user', function ($query) {
                $query->where('email', 'not like', 'paulcarvine%')
                    ->where('email', 'not like', 'x-guitar%')
                    ->where('email', 'not like', 'aleksnc%')
                    ->where('email', 'not like', '45rock%')
                    ->where('email', 'not like', 'domosy%')
                    ->where('email', 'not like', 'pavelyu%')
                    ->where('email', 'not like', 'tdostu%')
                    ->where('email', 'not like', 'notbeforeant%')
                    ->where('email', 'not like', 'lobanov%');
            })->count();

        foreach ($videoEffects as $videoEffect)
        {
            $countDownloadsFree = $videoEffect->user_downloads_free_count;
            $countDownloadsSubs = $videoEffect->user_downloads_subs_count;
            $trend_free = $countDownloadsFree > 0 ? min($countDownloadsFree / $countDownloadsTranding * 100, 1) : 0;
            $trend_subs = $countDownloadsSubs > 0 ? min($countDownloadsSubs / $countDownloadsTranding * 100, 1) : 0;

            $result = $trend_free * $coefficients['free_coefficient_video'] +
                $trend_subs * $coefficients['subs_coefficient_video'];

            VideoCoefficient::updateOrCreate([
                'video_effect_id' => $videoEffect->id,
            ], [
                'trending' => $result,
                'created_at' => $videoEffect->created_at,
                'downloads' => $videoEffect->user_downloads_count
            ]);
        }






        $tracks = SFXTrack::withCount(['userDownloads as user_downloads_free_count' => function($query) use ($coefficients) {
            $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->where('license_id', 5)
                ->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                });
        }])->withCount(['userDownloads as user_downloads_subs_count' => function($query) use ($coefficients) {
            $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->whereIn('license_id', [12, 13])
                ->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                });
        }])->withCount('userDownloads')->get();

        $countDownloadsTranding = UserDownloads::where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
            ->where('class', SFXTrack::class)->where(function($query) {
                $query->orWhereNotNull('billing_product_id')
                    ->orWhere(function ($query) {
                        $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                    });
            })->whereHas('user', function ($query) {
                $query->where('email', 'not like', 'paulcarvine%')
                    ->where('email', 'not like', 'x-guitar%')
                    ->where('email', 'not like', 'aleksnc%')
                    ->where('email', 'not like', '45rock%')
                    ->where('email', 'not like', 'domosy%')
                    ->where('email', 'not like', 'pavelyu%')
                    ->where('email', 'not like', 'tdostu%')
                    ->where('email', 'not like', 'notbeforeant%')
                    ->where('email', 'not like', 'lobanov%');
            })->count();

        foreach ($tracks as $track)
        {
            $countDownloadsFree = $track->user_downloads_free_count;
            $countDownloadsSubs = $track->user_downloads_subs_count;
            $trend_free = $countDownloadsFree > 0 ? min($countDownloadsFree / $countDownloadsTranding * 100, 1) : 0;
            $trend_subs = $countDownloadsSubs > 0 ? min($countDownloadsSubs / $countDownloadsTranding * 100, 1) : 0;

            $result = $trend_free * $coefficients['free_coefficient'] +
                $trend_subs * $coefficients['subs_coefficient'];

            SFXCoefficient::updateOrCreate([
                'sfx_id' => $track->id,
            ], [
                'trending' => $result,
                'created_at' => $track->created_at,
                'downloads' => $track->user_downloads_count
            ]);
        }
    }
}
