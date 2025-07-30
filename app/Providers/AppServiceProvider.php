<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Cache\CacheManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register the files service that Laravel 12 needs
        $this->app->singleton('files', function () {
            return new Filesystem;
        });

        // Register the Gate service that Laravel 12 needs
        $this->app->singleton(GateContract::class, function ($app) {
            return new Gate($app, function () use ($app) {
                return $app['auth']->user();
            });
        });

        // Register the cache service that Laravel 12 needs
        $this->app->singleton('cache', function ($app) {
            return new CacheManager($app);
        });
    }
}
