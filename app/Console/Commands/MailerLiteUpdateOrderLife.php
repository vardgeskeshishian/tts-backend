<?php

namespace App\Console\Commands;

use App\Exceptions\OrderLifeNoUserException;
use App\Exceptions\OrderLifeOrderNotFullException;
use App\Models\OrderLife;
use Carbon\Carbon;
use Illuminate\Console\Command;
use OrderLifeServiceFacade;

class MailerLiteUpdateOrderLife extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailer-lite:order-life';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates order_life field in mailer lite';

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

        $lives = OrderLife::with('order')->lazy();

        $lives->each(function (OrderLife $item) use ($startTime) {
            $order = $item->order;
            if (!$order) {
                $item->delete();

                return;
            }

            try {
                OrderLifeServiceFacade::setOrder($order)->deleteFinished()->sendUpdate();
            } catch (OrderLifeNoUserException|OrderLifeOrderNotFullException $e) {
            }
        });

        $executionTime = Carbon::now()->diffInMilliseconds($startTime);

        $this->info("Update order life. Total execution time was $executionTime");

	$pid = getmypid();
	exec("kill -9 {$pid}");
    }
}
