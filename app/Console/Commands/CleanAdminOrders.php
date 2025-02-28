<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Order;
use Illuminate\Console\Command;

class CleanAdminOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean-admin-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $admins = User::where('role', 'admin')->get()->pluck('id')->all();
        $orders = Order::whereIn('user_id', $admins);

        $count = $orders->count();

        $result = $this->output->ask("There is $count number of orders. Do you want to delete them?");

        if ($result === 'yes') {
            $orders->delete();
        }
    }
}
