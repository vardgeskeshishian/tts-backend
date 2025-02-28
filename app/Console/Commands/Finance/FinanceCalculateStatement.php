<?php

namespace App\Console\Commands\Finance;

use App\Models\Authors\Author;
use App\Services\Finance\FinanceService;
use App\Services\Finance\StatementService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FinanceCalculateStatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:calculate-statement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate statement finance every month';
    
    /**
     * @var StatementService
     */
    private StatementService $statementService;

    /**
     * Create a new command instance.
     *
     * @param StatementService $statementService
     */
    public function __construct(StatementService $statementService)
    {
        parent::__construct();
        $this->statementService = $statementService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $authors = Author::all()->lazy();

        $date = FinanceService::getFinanceDate(Carbon::now());

        foreach ($authors as $author) {
            $this->statementService->calculateAuthorShareForDate($author, $date);
        }
    }
}
