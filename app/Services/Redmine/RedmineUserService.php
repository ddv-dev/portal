<?php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RedmineUserService
{
    protected int $cacheTtl = 36000;

    public function findByLogin(string $login): ?RedmineUser
    {
        $login    = strtolower(trim($login));
        $cacheKey = "redmine_user_id_{$login}";

        $userId = Cache::remember($cacheKey, $this->cacheTtl, function () use ($login) {
            $user = RedmineUser::whereRaw('LOWER(login) = ?', [$login])->first();

            if ($user) {
                Log::info('Redmine user found', [
                    'login' => $login,
                    'id'    => $user->id,
                ]);
                return $user->id; 
            }

            Log::warning('Redmine user not found', ['login' => $login]);
            return null;
        });

        if ($userId === null || !is_int($userId)) {
            return null;
        }

        // Модель всегда достаём свежей из БД
        return RedmineUser::find($userId);
    }

    public function findIdByLogin(string $login): ?int
    {
        $login    = strtolower(trim($login));
        $cacheKey = "redmine_user_id_{$login}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($login) {
            $id = RedmineUser::whereRaw('LOWER(login) = ?', [$login])->value('id');

            if ($id !== null) {
                Log::info('Redmine user id cached', ['login' => $login, 'id' => $id]);
                return (int) $id;
            }

            Log::warning('Redmine user not found', ['login' => $login]);
            return null;
        });
    }

    public function forget(string $login): void
    {
        Cache::forget('redmine_user_id_' . strtolower(trim($login)));
        Cache::forget('redmine_user_' . strtolower(trim($login)));
    }
}
