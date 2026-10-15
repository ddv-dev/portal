<?php

namespace App\Observers;

use App\Models\Redmine\RedmineIssue;
use App\Services\Notifications\IssueNotifier;
use Illuminate\Support\Facades\Log;

class RedmineIssueObserver
{
    public function updated(RedmineIssue $issue): void
    {
        if (!$issue->wasChanged('status_id')) {
            return;
        }

        $oldStatusId = (int) $issue->getOriginal('status_id');
        $newStatusId = (int) $issue->status_id;

        if ($oldStatusId === $newStatusId) {
            return;
        }

        try {
            app(IssueNotifier::class)->onStatusChanged($issue, $oldStatusId, $newStatusId);
        } catch (\Throwable $e) {
            Log::error('RedmineIssueObserver: onStatusChanged failed', [
                'issue_id'      => $issue->id,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $newStatusId,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}