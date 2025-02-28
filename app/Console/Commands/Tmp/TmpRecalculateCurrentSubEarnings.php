<?php

namespace App\Console\Commands\Tmp;

use Illuminate\Console\Command;

/**
 * Class TmpRecalculateCurrentSubEarnings
 * @package App\Console\Commands\Tmp
 * @deprecated
 */
class TmpRecalculateCurrentSubEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:recalculate-current-sub-earnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate current sub earnings';

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
        //$authors = Author::all();

       //$statementService = resolve(StatementService::class);
       //$statsService = resolve(BalanceStatsService::class);

       //foreach($authors as $author) {
       //    $balances = Balance::where('user_id', $author->id)->get();

       //    $statsService->setUser($author);

       //    foreach($balances as $balance) {
       //        $authorShare = $statementService->getAuthorShareForDate($author, $balance->date);

       //        if ($balance->date === '2020-12') {
       //            $at = $statsService->calculateApproximateSubscriptionAward($balance->date, $authorShare);
       //        } else {
       //            $at = $statsService->calculateAbsoluteSubscriptionAward($balance->date, $authorShare);
       //        }

       //        $balance->details()
       //            ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
       //            ->where('user_type', FinancesEnv::USER_TYPE_PARTNER)
       //            ->update([
       //                'award' => 0
       //            ]);

       //        $balance->details()
       //            ->where('source_type', FinancesEnv::SOURCE_TYPE_A_DOWNLOAD)
       //            ->where('user_type', FinancesEnv::USER_TYPE_AUTHOR)
       //            ->update([
       //                'award' => round($at, 2)
       //            ]);

       //        $balance->author_balance = $balance->details->where('user_type', 'author')->sum('award');
       //        $balance->save();
       //    }
       //}

       //return 0;
    }
}
