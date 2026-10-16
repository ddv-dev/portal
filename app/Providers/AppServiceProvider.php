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
use App\Observers\NewsObserver;
use App\Services\Notifications\NotificationService;
use App\Observers\RedmineIssueObserver;

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

    // ===== View Composer: профиль + уведомления для шапки =====
        View::composer('partials.header', function ($view) {
            $view->with('profile', app(ProfileService::class)->headerData());

            $workerId = session('worker_id');

            // Список последних уведомлений (для сайдбара)
            $notifications = $workerId
                ? app(NotificationService::class)->forWorker($workerId, 20)
                : collect();

            // Счётчик непрочитанных
            $unreadCount = $workerId
                ? app(NotificationService::class)->unreadCount($workerId)
                : 0;

            $view->with([
                'notifications' => $notifications,
                'unreadCount'   => $unreadCount,
            ]);
        });

        // ===== Observer'ы =====
        RedmineIssue::observe(RedmineIssueObserver::class);
        News::observe(NewsObserver::class);
    }
}