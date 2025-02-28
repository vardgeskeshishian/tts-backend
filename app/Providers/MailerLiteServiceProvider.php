<?php


namespace App\Providers;

use App\Contracts\MailerLiteBatcherContract;
use App\Services\MailerLite\MailerLiteBatcher;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class MailerLiteServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MailerLiteBatcher::class, function ($app) {
            return new MailerLiteBatcher();
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
            MailerLiteBatcherContract::class,
        ];
    }
}
