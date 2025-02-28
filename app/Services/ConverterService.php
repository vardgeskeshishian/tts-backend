<?php


namespace App\Services;

use App\Models\User;
use CacheServiceFacade;

class ConverterService
{
    const CURRENCY_RUB = 'RUB';
    const CURRENCY_USD = 'USD';

    public function run(
        User $user,
        $priceToConvert,
        $force = false
    ): array {
        $countryCode = $user->country_code;

        $conversionNeeded = $force || strtolower($countryCode) === 'ru';

        $floatPrice = $conversionNeeded ? round($priceToConvert * CacheServiceFacade::getUsdRate(), 2) : $priceToConvert;

        return [
            'currency' => $conversionNeeded ? self::CURRENCY_RUB : self::CURRENCY_USD,
            'float_price' => $floatPrice,
            'price' => (string) $floatPrice,
        ];
    }
}
