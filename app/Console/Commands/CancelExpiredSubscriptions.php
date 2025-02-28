<?php

namespace App\Console\Commands;

use App\Services\MailerLite\MailerLiteService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\UserSubscription;

class CancelExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:cancel-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call Paddle api and cancel user subscriptions when the date is due';

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
     * @return mixed
     */
    public function handle()
    {
       $subscriptions = UserSubscription::where('status', 'active')
           ->whereNotNull('cancelled_at_paddle')
           ->where('expiring_at', '<=', Carbon::now())
           ->get();

       foreach ($subscriptions as $subscription) {
           $subscription->status = 'deleted';
           $subscription->save();

           resolve(MailerLiteService::class)->setUser($subscription->user)->updateUser();
       }

       return 0;
    }
}
