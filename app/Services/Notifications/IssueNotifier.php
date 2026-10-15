<?php

namespace App\Services\Notifications;

use App\Models\Redmine\RedmineIssue;
use App\Models\Redmine\RedmineJournal;
use App\Models\Redmine\RedmineIssueStatus;
use App\Models\Redmine\RedmineUser;
use App\Models\Worker;
use App\Notifications\Types\NotificationType;

class IssueNotifier
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function onComment(RedmineIssue $issue, RedmineJournal $journal, RedmineUser $author): void
    {
        $recipientWorkerId = $this->workerIdByRedmine($issue->author_id);
        if (!$recipientWorkerId) return;

        $actorWorkerId = $this->workerIdByRedmine($author->id);

        $this->notifications->notify(
            $recipientWorkerId,
            NotificationType::ISSUE_COMMENT,
            [
                'notifiable_type' => RedmineIssue::class,
                'notifiable_id'   => $issue->id,
                'title'           => "Новый комментарий к #{$issue->id}",
                'body'            => mb_substr((string) $journal->notes, 0, 300),
                'url'             => route('redmine.issues.show', $issue->id),
                'payload'         => [
                    'issue_id'   => $issue->id,
                    'journal_id' => $journal->id,
                ],
            ],
            $actorWorkerId,
            "issue_comment:{$journal->id}",
        );
    }

    public function onStatusChanged(RedmineIssue $issue, int $oldStatusId, int $newStatusId): void
    {
        $recipientWorkerId = $this->workerIdByRedmine($issue->author_id);
        if (!$recipientWorkerId) return;

        $old = RedmineIssueStatus::find($oldStatusId)?->name ?? "id={$oldStatusId}";
        $new = RedmineIssueStatus::find($newStatusId)?->name ?? "id={$newStatusId}";

        $this->notifications->notify(
            $recipientWorkerId,
            NotificationType::ISSUE_STATUS_CHANGED,
            [
                'notifiable_type' => RedmineIssue::class,
                'notifiable_id'   => $issue->id,
                'title'           => "Статус задачи #{$issue->id} изменён",
                'body'            => "{$old} → {$new}",
                'url'             => route('redmine.issues.show', $issue->id),
                'payload'         => [
                    'issue_id'      => $issue->id,
                    'old_status_id' => $oldStatusId,
                    'new_status_id' => $newStatusId,
                ],
            ],
            null,
            "issue_status:{$issue->id}:{$oldStatusId}->{$newStatusId}",
        );
    }

    protected function workerIdByRedmine(?int $redmineUserId): ?int
    {
        if (!$redmineUserId) return null;

        $workerId = Worker::where('redmine_user_id', $redmineUserId)->value('id');
        if ($workerId) return (int) $workerId;

        // fallback: через login_ad
        $login = \App\Models\Redmine\RedmineUser::whereKey($redmineUserId)->value('login');
        if (!$login) return null;

        return Worker::whereRaw('LOWER(login_ad) = ?', [strtolower($login)])->value('id');
    }
}