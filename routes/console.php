<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('redmine:sync-notifications --limit=1000')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->appendOutputTo(storage_path('logs/sync-notifications.log'));
/*
php artisan tinker --execute="
\$ls = DB::table('redmine_sync_state')->where('key','redmine_notifications_last_synced_at')->value('value');
\$max = App\Models\Redmine\RedmineJournal::where('journalized_type','Issue')->max('created_on');
echo 'last_synced: '.\$ls.PHP_EOL;
echo 'max journal: '.\$max.PHP_EOL;
echo 'backlog (sec): '.(\$max ? \Carbon\Carbon::parse(\$max)->diffInSeconds(\Carbon\Carbon::parse(\$ls)) : 0).PHP_EOL;
echo 'for 547: '.App\Models\Notification::where('worker_id', 547)->count().PHP_EOL;
" */