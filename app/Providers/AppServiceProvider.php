<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

use Illuminate\Pagination\Paginator;
use App\Auth\SsoGuard;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::extend('sso', function ($app, $name, array $config) {
            return new SsoGuard($app['request']);
        });
        Paginator::defaultView('vendor.pagination.custom');
    }
}
