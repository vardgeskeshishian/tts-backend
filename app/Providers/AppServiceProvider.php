<?php

namespace App\Providers;

use cijic\phpMorphy\Morphy;
use Exception;
use Carbon\Carbon;
use App\Vendor\Forks\PDFMerger;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws Exception
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Schema::defaultStringLength(191);
		
		if (!app()->environment('local')) {
			URL::forceScheme('https');
		}
        Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('U');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('PDFMerger', function ($app) {
            return new PDFMerger($app['files']);
        });

        $this->app->singleton(Morphy::class, function ($app) {
            return new Morphy('en');
        });
		if ($this->app->environment('local')) {
			$this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
			$this->app->register(TelescopeServiceProvider::class);
		}
    }
}
