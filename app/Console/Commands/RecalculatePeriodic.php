<?php

namespace App\Console\Commands;

use App\Constants\Env;
use App\Models\Coefficient;
use App\Models\OrderItem;
use App\Models\PeriodicCount;
use App\Models\Track;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculatePeriodic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculate-periodic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates periodical counts for tracks and video_effects';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $coeff = Coefficient::whereIn('short_name', ['rnd_t', 'rnd_ve', 'prd_days'])
            ->get()
            ->mapWithKeys(fn ($item) => [$item->short_name => $item->coefficient]);

        $periodStart = Carbon::now()->subDays($coeff['prd_days']);
        $periodEnd = Carbon::now();

        [$vfxSales, $vfxDownloads] = $this->getCountsForVFX($periodStart, $periodEnd);
        [$sales, $downloads] = $this->getCountsForTracks($periodStart, $periodEnd);

        PeriodicCount::truncate();
        $videoEffects = VideoEffect::select('id')->get();
        foreach ($videoEffects as $ve) {
            PeriodicCount::create([
                'item_id' => $ve->id,
                'item_type' => Env::ITEM_TYPE_VIDEO_EFFECTS,
                'sales' => $vfxSales[$ve->id] ?? 0,
                'downloads' => $vfxDownloads[$ve->id] ?? 0,
                'random' => rand(0, $coeff['rnd_ve']),
            ]);
        }

        $tracks = Track::select('id')->get();
        foreach ($tracks as $track) {
            PeriodicCount::create([
                'item_id' => $track->id,
                'item_type' => Env::ITEM_TYPE_TRACKS,
                'sales' => $sales[$track->id] ?? 0,
                'downloads' => $downloads[$track->id] ?? 0,
                'random' => rand(0, $coeff['rnd_t']),
            ]);
        }

        return 0;
    }

    private function getCountsForVFX(Carbon $periodStart, Carbon $periodEnd)
    {
        $sales = OrderItem::selectRaw('count(1) as n, item_id')
            ->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
            ->whereRelation('order', 'updated_at', '>=', $periodStart)
            ->whereRelation('order', 'updated_at', '<', $periodEnd)
            ->whereRelation('order', 'status', Env::STATUS_FINISHED)
            ->groupBy('item_id')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->item_id => $item->n]);

        $downloads = UserDownloads::selectRaw('count(1) as n, track_id')
            ->where('type', Env::ITEM_TYPE_VIDEO_EFFECTS)
            ->whereNotNull('license_id')
            ->where('created_at', '>=', $periodStart)
            ->where('created_at', '<', $periodEnd)
            ->groupBy('track_id')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->track_id => $item->n]);

        return [$sales, $downloads];
    }

    private function getCountsForTracks(Carbon $periodStart, Carbon $periodEnd)
    {
        $sales = OrderItem::selectRaw('count(1) as n, item_id')
            ->where('item_type', Env::ITEM_TYPE_TRACKS)
            ->whereRelation('order', 'updated_at', '>=', $periodStart)
            ->whereRelation('order', 'updated_at', '<', $periodEnd)
            ->whereRelation('order', 'status', Env::STATUS_FINISHED)
            ->groupBy('item_id')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->item_id => $item->n]);

        $downloads = UserDownloads::selectRaw('count(1) as n, track_id')
            ->whereIn('type', ['Creator', 'Business'])
            ->whereNotNull('license_id')
            ->where('created_at', '>=', $periodStart)
            ->where('created_at', '<', $periodEnd)
            ->groupBy('track_id')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->track_id => $item->n]);

        return [$sales, $downloads];
    }
}
