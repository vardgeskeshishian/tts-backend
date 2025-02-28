<?php

namespace App\Console\Commands;

use App\Services\MailerLite\MailerLiteBatcher;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;

class MailerLiteForceBatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailer-lite:force-batcher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force send batcher';

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
     * @throws BindingResolutionException
     */
    public function handle()
    {
        MailerLiteBatcher::getInstance()->sendBatch();
    }
}
