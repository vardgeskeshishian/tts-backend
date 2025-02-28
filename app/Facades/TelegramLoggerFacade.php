<?php


namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Contracts\TelegramLoggerContract;

class TelegramLoggerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TelegramLoggerContract::class;
    }
}
