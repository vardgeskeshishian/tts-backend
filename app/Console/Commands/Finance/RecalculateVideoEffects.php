<?php

namespace App\Console\Commands\Finance;

use App\Constants\FinancesEnv;
use App\Jobs\FillAuthorBalanceJob;
use App\Models\Authors\Author;
use App\Models\Order;
use App\Models\UserDownloads;
use App\Services\Finance\BalanceService;
use App\Services\Finance\FinanceService;
use App\Services\Finance\StatementService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RecalculateVideoEffects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:recalculate-video-effects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate statement finance every month';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $downloads = DB::select(DB::raw("
            select ud.id from user_downloads ud
                where
                    ud.created_at = '2023-02-01'
                    and type = 'video_effects' 
        "));

        foreach ($downloads as $ids) {
            $download = UserDownloads::find($ids->id);

            smart_dispatcher(
                (new FillAuthorBalanceJob())
                ->setUserDownload($download)
                ->setDate($download->created_at->format(FinancesEnv::BALANCE_DATE_FORMAT)
            ), [BalanceService::class]);
        }

        $orders = DB::select(
            DB::raw("
                select o.id from order_items oi
                left join orders o on oi.order_id = o.id
                    where item_type = 'video_effects' and o.status = 'finished' and o.created_at >= '2023-02-01'  
            ")
        );

        foreach ($orders as $ids) {
            $order = Order::find($ids->id);

            smart_dispatcher(
                (new FillAuthorBalanceJob())
                    ->setOrder($order)
                    ->setDate($order->created_at->format(FinancesEnv::BALANCE_DATE_FORMAT)
            ), [BalanceService::class]);
        }
    }
}
