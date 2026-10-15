<?php
// app/Console/Commands/BackfillWorkerRedmineIds.php

namespace App\Console\Commands;

use App\Models\Redmine\RedmineUser;
use App\Models\Worker;
use Illuminate\Console\Command;

class BackfillWorkerRedmineIds extends Command
{
    protected $signature = 'workers:backfill-redmine-ids {--dry-run}';
    protected $description = 'Проставить workers.redmine_user_id по login_ad = redmine.users.login';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $workers = Worker::query()
            ->whereNotNull('login_ad')
            ->whereNull('redmine_user_id')
            ->get(['id', 'login_ad', 'name_full']);

        if ($workers->isEmpty()) {
            $this->info('Нечего заполнять.');
            return self::SUCCESS;
        }

        $logins = $workers->pluck('login_ad')
            ->map(fn($l) => strtolower(trim($l)))
            ->unique()
            ->all();

        $redmine = RedmineUser::query()
            ->whereIn(\DB::raw('LOWER(login)'), $logins)
            ->get(['id', 'login'])
            ->keyBy(fn($u) => strtolower($u->login));

        $updated = 0;
        $skipped = 0;

        foreach ($workers as $worker) {
            $login  = strtolower(trim($worker->login_ad));
            $rmUser = $redmine->get($login);

            if (!$rmUser) {
                $this->line("  [skip] worker#{$worker->id} {$worker->login_ad} — нет в Redmine");
                $skipped++;
                continue;
            }

            $taken = Worker::where('redmine_user_id', $rmUser->id)
                ->where('id', '!=', $worker->id)
                ->exists();

            if ($taken) {
                $this->warn("  [warn] redmine#{$rmUser->id} уже привязан к другому Worker, пропуск worker#{$worker->id}");
                $skipped++;
                continue;
            }

            $this->line("  [ok]   worker#{$worker->id} {$worker->login_ad} → redmine#{$rmUser->id}");

            if (!$dry) {
                $worker->redmine_user_id = $rmUser->id;
                $worker->save();
            }
            $updated++;
        }

        $this->newLine();
        $this->info("Обновлено: {$updated}, пропущено: {$skipped}" . ($dry ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}