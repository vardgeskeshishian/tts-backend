<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class ResetCountersDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'counters:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'counters reset';

    public function handle(): void
    {
        DB::select("UPDATE users SET downloads = 0");
    }
}