<?php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineUser;
use App\Models\Redmine\RedmineProject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RedmineProjectService
{
    protected int $cacheTtl = 36000;

    public function getAvailableForUser(RedmineUser $user): Collection
    {

        return Cache::remember(
            "redmine_projects_user_{$user->id}",
            $this->cacheTtl,
            function () use ($user) {
                return RedmineProject::query()
                    ->where('projects.status', 1)
                    ->whereHas('enabledModules', fn($q) => $q->where('name', 'issue_tracking'))
                    ->where(function ($q) use ($user) {
                        $q->where('projects.is_public', 1)
                            ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
                    })
                    ->orderBy('projects.id')
                    ->get(['projects.id', 'projects.name', 'projects.identifier', 'projects.parent_id']);
            }
        );
    }

    public function forget(int $userId): void
    {
        Cache::forget("redmine_projects_user_{$userId}");
    }
}
