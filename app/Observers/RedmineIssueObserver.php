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

        $old = (int) $issue->getOriginal('status_id');
        $new = (int) $issue->status_id;

        if ($old === $new) {
            return;
        }

        try {
            app(IssueNotifier::class)->onStatusChanged($issue, $old, $new);
        } catch (\Throwable $e) {
            Log::error('RedmineIssueObserver: onStatusChanged failed', [
                'issue_id' => $issue->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}