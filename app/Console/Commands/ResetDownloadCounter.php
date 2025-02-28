<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetDownloadCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:download-limit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset free download counter';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        User::where('downloads', '>', 0)->update(['downloads' => 0]);
        return 0;
    }
}
