<?php


namespace App\Services\Finance;

use Cache;
use DateTime;
use Carbon\Carbon;
use App\Constants\FinancesEnv;

class FinanceService
{
    /**
     * @param $date
     *
     * @return string
     */
    public static function getFinanceDate($date)
    {
        $carbon = null;

        if ($date instanceof DateTime) {
            $date = $date->getTimestamp();
        }

        if ($date instanceof Carbon) {
            $date = $date->timestamp;
        }

        return Cache::remember("get-finance-date:{$date}", Carbon::now()->addMonth(), function () use ($date) {
            if (is_numeric($date)) {
                $carbon = Carbon::createFromTimestamp($date);
            } else {
                $carbon = Carbon::parse($date);
            }

            return $carbon->format(FinancesEnv::BALANCE_DATE_FORMAT);
        });
    }
}
