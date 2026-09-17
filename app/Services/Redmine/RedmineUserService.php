<?php
// app/Services/Redmine/RedmineUserService.php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RedmineUserService
{
    /**
     * Время кэша (секунды). 10 часов — как у AD-сервиса.
     */
    protected int $cacheTtl = 36000;

    /**
     * Найти Redmine-пользователя по логину.
     *
     * ВАЖНО: в кэше храним только int (ID), а не Eloquent-модель —
     * иначе при unserialize получаем __PHP_Incomplete_Class.
     */
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
                return $user->id; // <-- кэшируем ТОЛЬКО ID (int)
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

    /**
     * ID пользователя по логину (или null).
     */
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

    /**
     * Сбросить кэш по логину.
     */
    public function forget(string $login): void
    {
        Cache::forget('redmine_user_id_' . strtolower(trim($login)));
        // На всякий случай — старый ключ
        Cache::forget('redmine_user_' . strtolower(trim($login)));
    }
}