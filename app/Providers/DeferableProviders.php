<?php

namespace App\Providers;

use App\Contracts\CacheServiceContract;
use App\Services\Cache\CacheServiceMapper;
use App\Services\CacheService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class DeferableProviders extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CacheServiceContract::class, function ($app) {
            return new CacheService(resolve(CacheServiceMapper::class));
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
            CacheServiceContract::class,
        ];
    }
}
