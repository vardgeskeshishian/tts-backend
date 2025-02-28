<?php

namespace App\Console\Commands;

use App\Models\Paddle\Webhook\Webhook;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WebhookClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Webhook clear';

    /**
     * @return void
     */
    public function handle(): void
    {
        Webhook::where('created_at', '<=', Carbon::now()->subDays(14))->delete();
    }
}