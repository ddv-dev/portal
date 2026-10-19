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
        $since = $this->option('since')
            ? \Carbon\Carbon::parse($this->option('since'))
            : $this->getLastSyncedAt();

        $limit = (int) $this->option('limit');

        $this->info("Читаю journals с {$since->toDateTimeString()} (limit {$limit})");

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

            // === 1. Комментарий (есть notes) ===
            $hasNotes = !empty(trim((string) $journal->notes));

            if ($hasNotes) {
                if ($this->option('dry-run')) {
                    $this->line("  [comment] journal #{$journal->id} issue #{$issue->id} user #{$author->id}");
                } else {
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
            }

            // === 2. Смена статуса (есть details с prop_key = status_id) ===
            $statusDetails = $journal->details
                ->where('prop_key', 'status_id')
                ->first();

            if ($statusDetails && $statusDetails->old_value !== $statusDetails->value) {
                if ($this->option('dry-run')) {
                    $this->line("  [status]  journal #{$journal->id} issue #{$issue->id}: {$statusDetails->old_value} → {$statusDetails->value}");
                } else {
                    try {
                        app(IssueNotifier::class)->onStatusChanged(
                            $issue,
                            (int) $statusDetails->old_value,
                            (int) $statusDetails->value
                        );
                        $statusCount++;
                    } catch (\Throwable $e) {
                        Log::error('SyncRedmineNotifications: onStatusChanged failed', [
                            'journal_id' => $journal->id,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                }
            }

            if ($journal->created_on > $maxCreatedOn) {
                $maxCreatedOn = $journal->created_on;
            }
        }

        if (!$this->option('dry-run')) {
            $this->setLastSyncedAt($maxCreatedOn);

            $this->info("Готово. Комментариев: {$commentCount}, смен статуса: {$statusCount}, пропущено: {$skippedCount}");
        } else {
            $this->warn('DRY-RUN — ничего не записано');
        }

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