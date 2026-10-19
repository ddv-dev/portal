<?php

// app/Console/Commands/SyncRedmineNotifications.php

namespace App\Console\Commands;

use App\Models\Redmine\RedmineIssue;
use App\Models\Redmine\RedmineJournal;
use App\Models\Redmine\RedmineUser;
use App\Services\Notifications\IssueNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncRedmineNotifications extends Command
{
    protected $signature = 'redmine:sync-notifications
                            {--since= : Y-m-d H:i:s, с какой даты начинать (по умолчанию — last_synced)}
                            {--limit=500 : максимум journals за один прогон}
                            {--dry-run : только показать, что будет сделано}';

    protected $description = 'Синхронизирует комментарии и смены статуса из Redmine в уведомления портала';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            return $this->handleDryRun();
        }

        $batchSize  = max(100, (int) $this->option('limit'));
        $maxBatches = 20;
        $maxRuntime = 50;                    // секунд, чтобы не пересечься со следующим прогоном
        $startedAt  = microtime(true);

        $totalComments = 0;
        $totalStatuses = 0;
        $totalSkipped  = 0;
        $batches = 0;

        while ($batches < $maxBatches) {
            if ((microtime(true) - $startedAt) > $maxRuntime) {
                $this->warn("Достигнут лимит времени ({$maxRuntime}s) — выходим, следующий прогон продолжит.");
                break;
            }

            $since = $this->getLastSyncedAt();
            if ($since >= now()) {
                $this->info('Синк в актуальном состоянии.');
                break;
            }

            $journals = RedmineJournal::query()
                ->where('created_on', '>', $since)
                ->where('journalized_type', 'Issue')
                ->orderBy('created_on')
                ->limit($batchSize)
                ->get();

            if ($journals->isEmpty()) {
                $this->info('Нет новых журналов.');
                break;
            }

            $batches++;
            $maxCreatedOn = $since;
            $commentCount = 0;
            $statusCount  = 0;
            $skippedCount = 0;

            foreach ($journals as $journal) {
                $issue = RedmineIssue::find($journal->journalized_id);
                if (!$issue) {
                    $skippedCount++;
                    continue;
                }

                $author = RedmineUser::find($journal->user_id);
                if (!$author) {
                    $skippedCount++;
                    continue;
                }

                $hasNotes = !empty(trim((string) $journal->notes));
                if ($hasNotes) {
                    try {
                        app(IssueNotifier::class)->onComment($issue, $journal, $author);
                        $commentCount++;
                    } catch (\Throwable $e) {
                        Log::error('SyncRedmineNotifications: onComment failed', [
                            'journal_id' => $journal->id,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                }

                $statusDetails = $journal->details->where('prop_key', 'status_id')->first();
                if ($statusDetails && (int) $statusDetails->old_value !== (int) $statusDetails->value) {
                    try {
                        app(IssueNotifier::class)->onStatusChanged(
                            $issue,
                            (int) $statusDetails->old_value,
                            (int) $statusDetails->value,
                            $journal->id,                 // ← journal_id в dedup
                        );
                        $statusCount++;
                    } catch (\Throwable $e) {
                        Log::error('SyncRedmineNotifications: onStatusChanged failed', [
                            'journal_id' => $journal->id,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                }

                if ($journal->created_on > $maxCreatedOn) {
                    $maxCreatedOn = $journal->created_on;
                }
            }

            $this->setLastSyncedAt($maxCreatedOn);
            $this->line("Batch #{$batches}: журналов {$journals->count()}, комментариев {$commentCount}, статусов {$statusCount}, пропущено {$skippedCount}, last_synced → {$maxCreatedOn}");

            $totalComments += $commentCount;
            $totalStatuses += $statusCount;
            $totalSkipped  += $skippedCount;

            if ($journals->count() < $batchSize) {
                break;                            // догнали
            }
        }

        // мониторинг бэклога
        $maxCreatedOn = RedmineJournal::where('journalized_type', 'Issue')->max('created_on');
        $backlog = $maxCreatedOn
            ? \Carbon\Carbon::parse($maxCreatedOn)->diffInSeconds($this->getLastSyncedAt())
            : 0;
        if ($backlog > 120) {
            Log::warning('redmine:sync backlog', ['backlog_sec' => $backlog, 'last_synced' => $this->getLastSyncedAt()->toDateTimeString()]);
        }

        $this->info("Готово за {$batches} батч(ей). Комментариев: {$totalComments}, смен статуса: {$totalStatuses}, пропущено: {$totalSkipped}");
        return self::SUCCESS;
    }

    private function handleDryRun(): int
    {
        $since = $this->option('since')
            ? \Carbon\Carbon::parse($this->option('since'))
            : $this->getLastSyncedAt();

        $limit = (int) $this->option('limit');
        $this->info("DRY-RUN: читаю journals с {$since->toDateTimeString()} (limit {$limit})");

        $journals = RedmineJournal::query()
            ->where('created_on', '>', $since)
            ->where('journalized_type', 'Issue')
            ->orderBy('created_on')
            ->limit($limit)
            ->get();

        if ($journals->isEmpty()) {
            $this->info('Нет новых журналов.');
            return self::SUCCESS;
        }

        $this->info("Найдено журналов: {$journals->count()}");

        foreach ($journals as $journal) {
            $issue = RedmineIssue::find($journal->journalized_id);
            $author = RedmineUser::find($journal->user_id);
            if (!$issue || !$author) continue;

            if (!empty(trim((string) $journal->notes))) {
                $this->line("  [comment] journal #{$journal->id} issue #{$issue->id} user #{$author->id}");
            }

            $sd = $journal->details->where('prop_key', 'status_id')->first();
            if ($sd && (int) $sd->old_value !== (int) $sd->value) {
                $this->line("  [status]  journal #{$journal->id} issue #{$issue->id}: {$sd->old_value} → {$sd->value}");
            }
        }

        $this->warn('DRY-RUN — ничего не записано');
        return self::SUCCESS;
    }

    private function getLastSyncedAt(): \Carbon\Carbon
    {
        $value = DB::table('redmine_sync_state')
            ->where('key', 'redmine_notifications_last_synced_at')
            ->value('value');

        return $value ? \Carbon\Carbon::parse($value) : now()->subDay();
    }

    private function setLastSyncedAt(\Carbon\Carbon $at): void
    {
        DB::table('redmine_sync_state')->updateOrInsert(
            ['key' => 'redmine_notifications_last_synced_at'],
            ['value' => $at->toDateTimeString(), 'updated_at' => now()]
        );
    }
}
