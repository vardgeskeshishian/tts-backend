<?php

namespace App\Console\Commands\History;

use Illuminate\Console\Command;
use App\Services\Finance\BalanceService;

/**
 * Class HistoryFillAuthors
 * @package App\Console\Commands\History
 * @deprecated
 */
class HistoryFillAuthors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:fill-authors-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills old authors balance';

    /**
     * Create a new command instance.
     *
     * @param BalanceService $balanceService
     */
    public function __construct(BalanceService $balanceService)
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
        //$profiles = AuthorProfile::all();

       //Balance::truncate();
       //DetailedBalance::truncate();

       //foreach ($profiles as $profile) {
       //    $trackIds = Track::select('id')->where('author_profile_id', $profile->id)->get()->pluck('id');

       //    $this->output->comment("starting filling order items for {$profile->id}:{$profile->name}");
       //    $bar = $this->output->createProgressBar();

       //    OrderItem::where('item_type', Env::ITEM_TYPE_TRACKS)
       //        ->where(function ($q) use ($trackIds) {
       //            $q->whereIn('item_id', $trackIds)
       //                ->orWhereIn('track_id', $trackIds);
       //        })
       //        ->whereHas('order', function ($q) {
       //            $q->where('status', Env::STATUS_FINISHED)
       //                ->whereHas('user', function ($q) {
       //                    $q->where('role', '!=', 'admin');
       //                })
       //                ->orderBy('updated_at');
       //        })
       //        ->each(function (OrderItem $item) use ($profile, $bar) {
       //            if (!$item->order || $item->order->total === 0) {
       //                return true;
       //            }

       //            $bar->advance();

       //            $date = $item->created_at->format(FinancesEnv::BALANCE_DATE_FORMAT);

       //            $this->balanceService->setDate($date)
       //                ->award(
       //                    $profile->author_id,
       //                    $item->order->user_id,
       //                    FinancesEnv::SOURCE_TYPE_ORDER_ITEM,
       //                    $item->id,
       //                    50,
       //                    true,
       //                    $overRideData = [
       //                        'payment_type' => 'payoneer',
       //                        'payment_email' => $this->getPaymentInfoForUser($profile->author),
       //                    ]
       //                );

       //            $this->balanceService
       //                ->setCreatedAtDate($item->created_at);
       //        });

       //    $bar->finish();
       //}

       //foreach ($profiles as $profile) {
       //    $trackIds = Track::select('id')->where('author_profile_id', $profile->id)->get()->pluck('id');

       //    $this->output->comment("starting filling user downloads for {$profile->id}:{$profile->name}");

       //    $bar = $this->output->createProgressBar();

       //    UserDownloads::where('type', '!=', 'preview-download')
       //        ->whereNotNull('license_id')
       //        ->whereIn('track_id', $trackIds)
       //        ->each(function (UserDownloads $item) use ($profile, $bar) {
       //            $date = $item->created_at->format(FinancesEnv::BALANCE_DATE_FORMAT);

       //            $bar->advance();

       //            $this->balanceService->setDate($date)
       //                ->award(
       //                    $profile->author_id,
       //                    $item->user_id,
       //                    FinancesEnv::SOURCE_TYPE_A_DOWNLOAD,
       //                    $item->id,
       //                    50,
       //                    true,
       //                    $overRideData = [
       //                        'payment_type' => 'payoneer',
       //                        'payment_email' => $this->getPaymentInfoForUser($profile->author),
       //                    ]
       //                );
       //
       //            $this->balanceService->setCreatedAtDate($item->created_at);
       //        });

       //    $bar->finish();
       //}
    }

    private function getPaymentInfoForUser($user)
    {
        $email = $user->email;

        return match ($email) {
            'paulcarvine@gmail.com' => '45rock@mail.ru',
            'x-guitar@yandex.ru' => 'domosy@gmail.com',
            'prigidabreven@gmail.com' => 'belovruslanolegovich@gmail.com',
            'cosmonkey@list.ru' => 'illboy081@mail.ru',
            'runmoodmode@gmail.com', 'tunes.diamond@gmail.com' => 'vladkrotov1991@gmail.com',
            'freetaketones@gmail.com' => 'commercial',
            default => $email,
        };

    }
}
