<?php

namespace App\Console\Commands\History;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\Authors\Author;
use Illuminate\Console\Command;
use App\Services\Finance\FinanceService;
use App\Services\Finance\StatementService;

class HistoryFinanceStatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:recalculate-finance-statement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'History recalculates finance statement';

    /**
     * @var StatementService
     */
    protected StatementService $statementService;

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
        $startDate = new DateTime('2017-01');
        $endDate = new DateTime('2020-12');

        $interval = new DateInterval('P1M');
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        $authors = Author::all()->lazy();

        foreach ($dateRange as $date) {
            $date = FinanceService::getFinanceDate($date);

            foreach ($authors as $author) {
                $this->statementService->calculateAuthorShareForDate($author, $date);
            }
        }
    }
}
