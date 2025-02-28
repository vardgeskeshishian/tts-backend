<?php

namespace App\Facades;

use App\Contracts\OrderLifeServiceContract;
use Illuminate\Support\Facades\Facade;

class OrderLifeServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderLifeServiceContract::class;
    }
}
