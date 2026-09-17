<?php
// app/Http/Middleware/WindowsAuthentication.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ActiveDirectoryService;
use App\Services\Redmine\RedmineUserService;

class WindowsAuthentication
{
    public function __construct(
        private ActiveDirectoryService $ad,
        private RedmineUserService $redmineUsers,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Уже авторизованы в сессии — пропускаем
        if (session()->has('windows_user')) {
            return $next($request);
        }

        $username = $_SERVER['REMOTE_USER']
            ?? $_SERVER['GSSAPI_NAME']
            ?? $_SERVER['PHP_AUTH_USER']
            ?? null;

        if (empty($username)) {
            Log::warning('SSO: REMOTE_USER пуст — Apache не передал пользователя', [
                'server_keys' => array_filter(
                    array_keys($_SERVER),
                    fn($k) => str_contains($k, 'AUTH') || str_contains($k, 'USER') || str_contains($k, 'GSS')
                ),
            ]);

            return response('Unauthorized', 401);
        }

        // Нормализация логина: M01\d.demekhin / d.demekhin@M01.DZM / M01-d.demekhin -> d.demekhin
        $username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
        $username = preg_replace('/^.*\\\\/', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', trim($username));

        // 1. AD
        $info = Cache::remember("ad_info_{$username}", 36000, function () use ($username) {
            return $this->ad->getUserInfo($username);
        });

        $fio = $info['fio'] ?? $username;

        // 2. Redmine (MySQL) — ищем по логину
        $redmineUser = $this->redmineUsers->findByLogin($username);

        // 3. Кладём в сессию
        session([
            'windows_user'         => $username,
            'windows_fio'          => $fio,
            'windows_department'   => $info['department'] ?? null,
            'windows_email'        => $info['email']      ?? null,
            'windows_title'        => $info['title']      ?? null,

            'redmine_user_id'      => $redmineUser?->id,
            'redmine_login'        => $redmineUser?->login,
            'redmine_fio'          => $redmineUser?->full_name,
        ]);

        $request->attributes->set('windows_user', $username);
        $request->merge(['windows_user' => $username]);

        Log::info('SSO auth success', [
            'user'        => $username,
            'windows_fio' => $fio,
            'redmine_id'  => $redmineUser?->id,
        ]);

        return $next($request);
    }
}