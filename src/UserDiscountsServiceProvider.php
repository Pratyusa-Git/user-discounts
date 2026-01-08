<?php

namespace Devpratyusa\UserDiscounts;

use Illuminate\Support\ServiceProvider;

class UserDiscountsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/user-discounts.php' => config_path('user-discounts.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/user-discounts.php', 'user-discounts');

        $this->app->singleton('Devpratyusa\UserDiscounts\Services\DiscountManager', function () {
            return new \Devpratyusa\UserDiscounts\Services\DiscountManager;
        });
    }
}
