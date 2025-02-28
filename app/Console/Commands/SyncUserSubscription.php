<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Services\PaddleService;
use App\Models\UserSubscription;
use Illuminate\Console\Command;

class SyncUserSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:user-sync {--email=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise expiring_at time between paddle and us';

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
        $service = app(PaddleService::class);

        if (!$this->option('email') && !$this->option('all')) {
            return;
        }

        if ($this->option('all')) {
            $subscriptions = $service->getSubscribedUsers();
        } else {
            $subscriptions = $service->getSubscribedUsers($this->option('email'));
        }

        $this->info(count($subscriptions));

        foreach ($subscriptions as $index => $subscription) {
            $this->syncSubscriptionExpiringDate($subscription);
        }

        $this->info(count($subscriptions));
    }

    private function syncSubscriptionExpiringDate($paddleSubscription)
    {
        $subscription = UserSubscription::where('subscription_id', $paddleSubscription->subscription_id)->first();

        if (!$subscription) {
            return;
        }

        $expiringAt = $paddleSubscription->next_payment->date;
        $oldExpiringAt = Carbon::parse($subscription->expiring_at)->format('Y-m-d');

        $this->info('/-------------------------------/');
        $this->info('p:' . $expiringAt . ' -  ' . 't:' . $oldExpiringAt . ' - ' . $paddleSubscription->state . ' - ' . $subscription->status);
        $this->info($subscription->user->id . ' - ' . $subscription->user->email);
        $subscription->expiring_at = $expiringAt;
        if ($oldExpiringAt !== $expiringAt) {
            $this->info('expiring date are not equal');
        }
        if ($paddleSubscription->state === 'deleted' && $subscription->status === 'active') {
            $this->info('actual status is deleted');
        }
        $subscription->save();
    }
}
