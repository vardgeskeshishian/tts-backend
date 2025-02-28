<?php

namespace App\Console\Commands\Finance;

use App\Constants\Env;
use Carbon\Carbon;
use App\Models\Authors\Author;
use App\Constants\FinancesEnv;
use Illuminate\Console\Command;
use App\Models\Finance\Balance;
use App\Services\Finance\FinanceService;
use App\Services\Finance\BalanceStatsService;

class FinanceCalculateAbsoluteSubsciptionAwardForAuthors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:calculate-absolute-subscription-award';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Substitute approximate award for absolute for authors ';

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
     * @param BalanceStatsService $statsService
     * @return int
     */
    public function handle(BalanceStatsService $statsService)
    {
        $authors = Author::all();
        $currentMonth = Carbon::now()->startOfMonth();
        $date = Carbon::now()->previous('month');

        $result = $statsService->calculateAbsoluteSubscriptionAward($date);

        foreach ($authors as $author) {
            $statsService->setUser($author);

            $currentBalance = Balance::where([
                'user_id' => $author->id,
                'status' => FinancesEnv::BALANCE_STATUS_AWAITING,
                'date' => FinanceService::getFinanceDate($date)
            ])->first();

            // update with absolute award
            if ($currentBalance) {
                $currentBalance->details()
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->where('item_type', Env::ITEM_TYPE_TRACKS)
                    ->where('rate', 50)
                    ->update([
                        'award' => round($result['audio']['exc'], 2)
                    ]);

                $currentBalance->details()
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->where('item_type', Env::ITEM_TYPE_TRACKS)
                    ->where('rate', 40)
                    ->update([
                        'award' => round($result['audio']['non-exc'], 2)
                    ]);

                $currentBalance->details()
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->where('rate', 50)
                    ->update([
                        'award' => round($result['video']['exc'], 2)
                    ]);

                $currentBalance->details()
                    ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
                    ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
                    ->where('item_type', Env::ITEM_TYPE_VIDEO_EFFECTS)
                    ->where('rate', 40)
                    ->update([
                        'award' => round($result['video']['non-exc'], 2)
                    ]);

                $currentBalance->author_balance = $currentBalance->details->where('user_type', 'author')->sum('award');
                $currentBalance->save();
            }

            // create new balance for current month
            Balance::firstOrCreate([
                'date' => FinanceService::getFinanceDate($currentMonth),
                'user_id' => $author->id,
                'status' => FinancesEnv::BALANCE_STATUS_AWAITING,
                'payment_type' => $currentBalance?->payment_type,
                'payment_email' => $currentBalance?->payment_email,
            ]);
        }
    }
}
