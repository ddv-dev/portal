<?php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineIssue;
use App\Models\Redmine\RedmineJournal;
use App\Models\Redmine\RedmineUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\Redmine\RedmineTracker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RedmineIssueService
{
    public function getUserIssues(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return RedmineIssue::forUser($userId)
            ->with(['project:id,name', 'status:id,name,is_closed', 'tracker:id,name', 'priority:id,name'])
            ->orderByDesc('updated_on')
            ->paginate($perPage);
    }


    public function getHistory(int $issueId): Collection
    {
        return RedmineJournal::where('journalized_id', $issueId)
            ->where('journalized_type', 'Issue')
            ->with(['user:id,login,firstname,lastname', 'details'])
            ->orderBy('created_on')
            ->get();
    }


    public function createIssue(RedmineUser $author, array $data): ?RedmineIssue
    {
        $required = ['project_id', 'tracker_id', 'subject'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Log::warning('Redmine issue create: missing field', ['field' => $field, 'data' => $data]);
                return null;
            }
        }

        // Статус по умолчанию для трекера
        $statusId = (int) (\App\Models\Redmine\RedmineTracker::where('id', $data['tracker_id'])
            ->value('default_status_id') ?? 0);

        if (!$statusId) {
            // Фолбэк: первый открытый статус
            $statusId = (int) (\App\Models\Redmine\RedmineIssueStatus::where('is_closed', 0)
                ->orderBy('position')
                ->value('id') ?? 1);
        }

        // Приоритет по умолчанию
        $priorityId = (int) (\App\Models\Redmine\RedmineEnumeration::ofType('IssuePriority')
            ->active()
            ->where('is_default', 1)
            ->value('id') ?? 0);

        if (!$priorityId) {
            // Фолбэк: первый активный
            $priorityId = (int) (\App\Models\Redmine\RedmineEnumeration::ofType('IssuePriority')
                ->active()
                ->orderBy('position')
                ->value('id') ?? 2);
        }

        try {
            return DB::connection('redmine')->transaction(function () use ($author, $data, $statusId, $priorityId) {
                $now = now();

                $issue = RedmineIssue::create([
                    'tracker_id'      => $data['tracker_id'],
                    'project_id'      => $data['project_id'],
                    'subject'         => $data['subject'],
                    'description'     => $data['description'] ?? '',
                    'status_id'       => $statusId,
                    'priority_id'     => $priorityId,
                    'author_id'       => $author->id,
                    'assigned_to_id'  => $data['assigned_to_id'] ?? null,
                    'category_id'     => $data['category_id'] ?? null,
                    'fixed_version_id' => $data['fixed_version_id'] ?? null,
                    'start_date'      => $data['start_date'] ?? null,
                    'due_date'        => $data['due_date'] ?? null,
                    'done_ratio'      => $data['done_ratio'] ?? 0,
                    'estimated_hours' => $data['estimated_hours'] ?? null,
                    'is_private'      => $data['is_private'] ?? 0,
                    'lock_version'    => 0,
                    'created_on'      => $now,
                    'updated_on'      => $now,
                    'lft'             => 1,
                    'rgt'             => 2,
                ]);

                Log::info('Redmine issue created', [
                    'id'           => $issue->id,
                    'author_id'    => $author->id,
                    'project_id'   => $data['project_id'],
                    'tracker_id'   => $data['tracker_id'],
                    'status_id'    => $statusId,
                    'priority_id'  => $priorityId,
                ]);

                return $issue;
            });
        } catch (\Throwable $e) {
            Log::error('Redmine issue create failed', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
            return null;
        }
    }


    public function addComment(int $issueId, RedmineUser $user, string $notes, bool $private = false): ?RedmineJournal
    {
        if (trim($notes) === '') {
            return null;
        }

        try {
            return DB::connection('redmine')->transaction(function () use ($issueId, $user, $notes, $private) {
                $now = now();

                $journal = RedmineJournal::create([
                    'journalized_id'   => $issueId,
                    'journalized_type' => 'Issue',
                    'user_id'          => $user->id,
                    'notes'            => $notes,
                    'created_on'       => $now,
                    'private_notes'    => $private ? 1 : 0,
                ]);

                // Обновляем updated_on у задачи, чтобы Redmine её «увидел» как изменённую
                RedmineIssue::where('id', $issueId)->update(['updated_on' => $now]);

                Log::info('Redmine comment added', [
                    'issue_id'   => $issueId,
                    'journal_id' => $journal->id,
                    'user_id'    => $user->id,
                ]);

                return $journal;
            });
        } catch (\Throwable $e) {
            Log::error('Redmine comment failed', [
                'issue_id' => $issueId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }


    public function getFormDictionaries(): array
    {
        return [
            'trackers' => \App\Models\Redmine\RedmineTracker::orderBy('position')->get(['id', 'name']),
            'statuses' => \App\Models\Redmine\RedmineIssueStatus::orderBy('position')->get(['id', 'name', 'is_closed']),
            'priorities' => \App\Models\Redmine\RedmineEnumeration::ofType('IssuePriority')
                ->active()
                ->orderBy('position')
                ->get(['id', 'name', 'is_default']),
        ];
    }

    public function getTrackersForProject(int $projectId): Collection
    {
        // Трекеры, разрешённые проекту через projects_trackers.
        // Если для проекта нет ни одной записи — считаем, что доступны все.
        $hasRestrictions = DB::connection('redmine')
            ->table('projects_trackers')
            ->where('project_id', $projectId)
            ->exists();

        if (!$hasRestrictions) {
            return RedmineTracker::query()
                ->orderBy('position')
                ->get(['id', 'name']);
        }

        return RedmineTracker::query()
            ->whereIn('id', function ($q) use ($projectId) {
                $q->select('tracker_id')
                    ->from('projects_trackers')
                    ->where('project_id', $projectId);
            })
            ->orderBy('position')
            ->get(['id', 'name']);
    }
    public function getUserIssuesInProject(int $userId, int $projectId, string $filter = 'open')
    {
        $query = RedmineIssue::query()
            ->where('project_id', $projectId)
            ->where('author_id', $userId)
            ->with([
                'status:id,name,is_closed',
                'tracker:id,name',
                'priority:id,name',
                'assignee:id,login,firstname,lastname',
            ])
            ->orderByDesc('updated_on');

        if ($filter === 'open') {
            $query->whereNull('closed_on');
        } elseif ($filter === 'closed') {
            $query->whereNotNull('closed_on');
        }

        return $query->limit(200)->get();
    }
}
