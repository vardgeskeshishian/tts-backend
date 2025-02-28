<?php


namespace App\Console\Commands;

use App\Models\SubscriptionHistory;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
+-------+---------------------------------+-----------------+---------------------+---------------------+---------------------+---------+-----------+
| id    | email                           | subscription_id | created_at          | expiring_at         | cancelled_at_paddle | plan_id | plan      |
+-------+---------------------------------+-----------------+---------------------+---------------------+---------------------+---------+-----------+
|   174 | anthonyf@eview360.com           |         2034491 | 2019-09-29 10:58:16 | 2021-05-03 13:02:14 | NULL                |      12 | 1 Month   |
| 55586 | dj@crop.agency                  |         2889817 | 2020-03-16 23:14:54 | 2021-05-03 13:02:14 | NULL                |      12 | 1 Month   |
| 41570 | jeff@jpearsonphoto.com          |         3294568 | 2020-04-29 11:49:26 | 2021-05-03 13:02:14 | NULL                |      12 | 1 Month   |
| 30569 | sarattran@hotmail.com           |         3321126 | 2020-05-02 05:47:41 | 2021-05-03 13:02:14 | NULL                |      12 | 1 Month   |
| 57448 | mwigboutique@gmail.com          |         3372976 | 2020-05-08 14:32:12 | 2021-05-03 13:02:14 | NULL                |      13 | 3 Months  |
| 57486 | philipwilhite@gmail.com         |         3380718 | 2020-05-09 15:11:33 | 2021-05-03 13:02:14 | NULL                |      12 | 1 Month   |
| 21855 | sergeystunt@gmail.com           |         6821961 | 2020-10-10 15:56:21 | 2021-05-03 13:02:14 | 2021-05-02 08:51:06 |      12 | 1 Month   |
| 61751 | tdostupa+13@gmail.com           |         4823332 | 2020-10-17 08:28:37 | 2021-05-03 13:02:14 | NULL                |      13 | 3 Months  |
| 64530 | jared@thenearsky.com            |         5969926 | 2021-01-25 02:28:11 | 2021-05-03 13:02:14 | 2021-04-21 13:52:12 |      12 | 1 Month   |
| 64689 | passal9@gmail.com               |         6025459 | 2021-01-29 10:56:28 | 2021-05-03 13:02:14 | 2021-04-19 07:14:06 |      12 | 1 Month   |
| 64731 | arctos@aol.com                  |         6050972 | 2021-01-31 15:36:18 | 2021-05-03 13:02:14 | 2021-04-23 01:36:33 |      13 | 3 Months  |
| 65400 | cathyli@ilaluz.com              |         6262511 | 2021-02-17 10:49:30 | 2021-05-03 13:02:14 | NULL                |      47 | 1 Month   |
| 65716 | nwo4life75604@yahoo.com         |         6441881 | 2021-03-03 20:47:56 | 2021-05-08 20:40:49 | 2021-05-08 20:40:49 |      12 | 1 Month   |
| 65831 | tanya@renaissancenyc.com        |         6493996 | 2021-03-08 00:36:30 | 2021-05-19 12:54:06 | 2021-05-19 12:54:06 |      12 | 1 Month   |
| 65942 | luciapapir@gmail.com            |         6567386 | 2021-03-13 17:26:17 | 2021-05-09 15:20:24 | 2021-05-09 15:20:24 |      12 | 1 Month   |
| 65963 | nickshoe257@gmail.com           |         6579441 | 2021-03-14 18:04:07 | 2021-05-03 13:02:14 | 2021-04-26 16:02:52 |      12 | 1 Month   |
| 65995 | nathan@creativeabm.com          |         6597060 | 2021-03-15 23:45:12 | 2021-05-03 13:02:14 | 2021-04-20 22:09:25 |      12 | 1 Month   |
| 66191 | dlawler1@me.com                 |         6688270 | 2021-03-23 04:24:21 | 2021-05-03 13:02:14 | 2021-04-23 05:10:11 |      12 | 1 Month   |
| 66196 | marketing@einas.sa              |         6690772 | 2021-03-23 10:40:48 | 2021-05-03 13:02:14 | 2021-04-23 10:44:10 |      47 | 1 Month   |
| 66389 | shannon@asoae.net               |         6800663 | 2021-03-31 23:37:58 | 2021-05-03 13:02:14 | 2021-04-28 18:25:26 |      12 | 1 Month   |
| 60170 | camcale10@gmail.com             |         6869541 | 2021-04-06 21:58:23 | 2021-05-10 16:45:23 | 2021-05-10 16:45:23 |      12 | 1 Month   |
| 66759 | www.jacobjackson@gmail.com      |         6934687 | 2021-04-12 12:33:42 | 2021-05-03 13:02:14 | 2021-04-20 03:53:08 |      12 | 1 Month   |
| 66765 | tima.chashurin@gmail.com        |         6936623 | 2021-04-12 15:15:32 | 2021-05-12 14:49:56 | 2021-05-12 14:49:56 |      12 | 1 Month   |
| 66789 | glenda@palmierogioielli.com     |         6944653 | 2021-04-13 06:53:10 | 2021-05-13 06:42:19 | 2021-05-13 06:42:19 |      12 | 1 Month   |
| 66889 | tammyzdunich@gmail.com          |         6975837 | 2021-04-15 16:54:02 | 2021-05-03 13:02:14 | 2021-04-19 02:26:56 |      12 | 1 Month   |
| 66959 | tayloralexmitchell@gmail.com    |         6995437 | 2021-04-17 10:27:13 | 2021-05-12 12:23:44 | 2021-05-12 12:23:44 |      12 | 1 Month   |
| 66994 | savannahslifer@gmail.com        |         7013271 | 2021-04-19 00:03:26 | 2021-05-14 07:20:38 | 2021-05-14 07:20:38 |      13 | 3 Months  |
| 67045 | shane@coldcreator.com           |         7024714 | 2021-04-19 21:35:30 | 2021-05-03 13:02:14 | 2021-04-28 23:40:20 |      47 | 1 Month   |
| 67183 | metroplexx2468@gmail.com        |         7062166 | 2021-04-22 20:47:12 | 2021-05-03 13:02:14 | 2021-04-22 23:36:14 |      12 | 1 Month   |
| 67391 | patrick.geis@equippers-mainz.de |         7124884 | 2021-04-28 07:39:46 | 2021-05-04 13:15:45 | 2021-05-04 13:15:45 |      12 | 1 Month   |
| 67497 | michaeltravers61@gmail.com      |         7162158 | 2021-05-01 01:35:17 | 2021-05-03 13:02:14 | 2021-05-01 01:53:26 |      12 | 1 Month   |
| 67773 | dradramoor@gmail.com            |         7247883 | 2021-05-07 19:17:06 | 2021-05-07 19:46:20 | 2021-05-07 19:46:20 |      15 | 12 Months |
| 67917 | harkirat20@gmail.com            |         7300538 | 2021-05-12 04:04:22 | 2021-05-12 04:04:59 | 2021-05-12 04:04:59 |      12 | 1 Month   |
+-------+---------------------------------+-----------------+---------------------+---------------------+---------------------+---------+-----------+
**/
class FixUserSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:fix';

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
        UserSubscription::where('expiring_at', '<', now())->where('status', 'active')
            ->each(function (UserSubscription $subscription) {
                $plan = strtolower($subscription->plan->plan);

                $date = null;

                dump($plan);

                switch ($plan) {
                    case '1 month':
                        $date = ['months', 1];
                        break;
                    case '3 months':
                        $date = ['months', 3];
                        break;
                    case '6 months':
                        $date = ['months', 6];
                        break;
                    case '12 months':
                        $date = ['year', 1];
                        break;
                }

                /**
                 * @var $latest SubscriptionHistory
                 */
                $latest = $subscription->history()->latest()->first();

                if (!$latest || $latest->created_at->add($date[0], $date[1])->lt(Carbon::now())) {
                    $subscription->status = 'deleted';
                }

                $subscription->expiring_at = $latest->created_at->add($date[0], $date[1]);
                $subscription->save();

                $this->info("subscription for user: {$subscription->user_id} should be active until: {$latest->created_at->add($date[0], $date[1])}, {$subscription->expiring_at}");
            });
        //UserSubscription::where('status', 'active')
        //    ->where('expiring_at', '<=', Carbon::now());

        return 0;
    }
}
