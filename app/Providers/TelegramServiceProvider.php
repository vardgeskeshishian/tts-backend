<?php

namespace App\Providers;

use App\Services\TelegramLoggerService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\TelegramLoggerContract;
use Illuminate\Contracts\Support\DeferrableProvider;

class TelegramServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TelegramLoggerContract::class, function ($app) {
            return new TelegramLoggerService();
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
            TelegramLoggerContract::class,
        ];
    }
}
