<?php

namespace App\Providers;

use App\Contracts\OrderLifeServiceContract;
use App\Services\OrderLifeService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class OrderLifeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OrderLifeServiceContract::class, function ($app) {
            return new OrderLifeService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function provides()
    {
        return [
            OrderLifeServiceContract::class,
        ];
    }
}
