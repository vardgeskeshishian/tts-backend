<?php

namespace App\Facades;

use App\Contracts\CacheServiceContract;
use Illuminate\Support\Facades\Facade;

class CacheServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CacheServiceContract::class;
    }
}
