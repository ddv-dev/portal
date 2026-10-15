<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Auth\SsoGuard;
use App\Services\ProfileService;
use App\Models\Redmine\RedmineIssue;
use App\Models\News;
use App\Observers\RedmineIssueObserver;
use App\Observers\NewsObserver;

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

        View::composer('partials.header', function ($view) {
            $view->with('profile', app(ProfileService::class)->headerData());
        });

        RedmineIssue::observe(RedmineIssueObserver::class);
        News::observe(NewsObserver::class); 
    }
}