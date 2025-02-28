<?php

namespace App\Console\Commands;

use App\Constants\Env;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MailerLitePushActiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailer-lite:push-active-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects and pushes active users to mailer lite';

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
        $userIds = DB::table('orders')
                     ->selectRaw('distinct(user_id)')
                     ->where('status', Env::STATUS_FINISHED)
                     ->get('user_id');

        $chunks = $userIds->chunk(100);

        foreach ($chunks as $items) {
            $users = User::with('bought')->whereIn('id', $items->pluck('user_id'))->get();

            foreach ($users as $user) {
                dd([
                    'purchase_count' => $user->bought->count(),
                    'purchase_price' => $user->bought->sum('price'),
                ]);
            }
        }
    }
}
