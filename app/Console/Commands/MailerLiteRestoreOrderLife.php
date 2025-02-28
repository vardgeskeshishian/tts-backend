<?php

namespace App\Console\Commands;

use App\Constants\Env;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use OrderLifeServiceFacade;

class MailerLiteRestoreOrderLife extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailer-lite:restore-order-life';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore order_life field in mailer lite';

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
        $startTime = Carbon::now();

        $this->info("Update order life Started at $startTime");

        Order::where('type', Env::ORDER_TYPE_FULL)
            ->where('created_at', '>', Carbon::now()->subMonths(2))
            ->each(function (Order $order) {
                OrderLifeServiceFacade::setOrder($order)->createFromOrderCreationDate();
            });

        return 0;
    }
}
