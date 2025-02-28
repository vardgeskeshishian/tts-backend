<?php

namespace App\Console\Commands;

use App\Imports\ContentImport;
use App\Models\UserSubscription;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class SyncUserSubscriptionClassic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-classic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'users:sync-classic';

    public function handle()
    {
        $array = Excel::toArray(new ContentImport(), base_path('/public_html/paddle_classic_clean.csv'));

        foreach ($array[0] as $row) {
            UserSubscription::where('subscription_id', $row['subscription_id'])
                ->update([
                    'update_url' => $row['update_url'],
                    'cancel_url' => $row['cancel_url'],
                    'status' => $row['status'],
                    'expiring_at' => $row['expiring_at'],
                    'cancelling_at' => $row['cancelling_at'],
                    'cancelled_at_paddle' => $row['cancelled_at_paddle']
                ]);
        }
    }
}