<?php
// app/Services/Redmine/RedmineProjectService.php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineProject;
use App\Models\Redmine\RedmineUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class RedmineProjectService
{
    protected int $cacheTtl = 36000; 

    /**
     * Проекты, доступные пользователю:
     *  - активные (status = 1)
     *  - с включённым модулем issue_tracking
     *  - где пользователь member ИЛИ проект публичный
     */
    public function getAvailableForUser(RedmineUser $user): Collection
    {
        $cacheKey = "redmine_project_ids_user_{$user->id}";

        // В кэш кладём ТОЛЬКО массив ID (int) — безопасно для сериализации
        $projectIds = Cache::remember($cacheKey, $this->cacheTtl, function () use ($user) {
            $ids = RedmineProject::query()
                ->where('projects.status', 1)
                ->whereHas('enabledModules', fn($q) => $q->where('name', 'issue_tracking'))
                ->where(function ($q) use ($user) {
                    $q->where('projects.is_public', 1)
                        ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
                })
                ->orderBy('projects.id')
                ->pluck('projects.id')
                ->map(fn($id) => (int) $id)
                ->all();

            Log::info('Redmine projects cached', [
                'user_id' => $user->id,
                'count'   => count($ids),
            ]);

            return $ids;
        });

        if (empty($projectIds)) {
            return collect();
        }

        // Модели достаём свежими из БД и сохраняем порядок
        return RedmineProject::query()
            ->whereIn('projects.id', $projectIds)
            ->orderBy('projects.id')
            ->get([
                'projects.id',
                'projects.name',
                'projects.identifier',
                'projects.parent_id',
                'projects.description',
            ]);
    }



    /**
     * Сбросить кэш проектов пользователя.
     */
    public function forget(int $userId): void
    {
        Cache::forget("redmine_project_ids_user_{$userId}");
    }
}
