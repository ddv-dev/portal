<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ActiveDirectoryService;
use Illuminate\Support\Facades\Cache;

class WindowsAuthentication
{

    public function __construct(
        private ActiveDirectoryService $ad
    ) {}


    public function handle(Request $request, Closure $next)
    {
        if (session()->has('windows_user')) {
            return $next($request);
        }

        $username = $_SERVER['REMOTE_USER']
            ?? $_SERVER['GSSAPI_NAME']
            ?? $_SERVER['PHP_AUTH_USER']
            ?? null;

        if (empty($username)) {
            Log::warning('SSO: REMOTE_USER пуст — Apache не передал пользователя', [
                'server_keys' => array_filter(array_keys($_SERVER), fn($k) => str_contains($k, 'AUTH') || str_contains($k, 'USER') || str_contains($k, 'GSS')),
            ]);

            return response('Unauthorized', 401);
        }

        $username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
        $username = preg_replace('/^.*\\\\/', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', trim($username));

        $info = Cache::remember("ad_info_{$username}", 36000, function () use ($username) {
            return $this->ad->getUserInfo($username);
        });

        session([
            'windows_user'         => $username,
            'windows_fio'          => $info['fio']        ?? $username,
            'windows_department'   => $info['department'] ?? null,
            'windows_email'        => $info['email']      ?? null,
            'windows_title'        => $info['title']      ?? null,
        ]);

        $request->attributes->set('windows_user', $username);
        $request->merge(['windows_user' => $username]);


        Log::info('SSO auth success', ['user' => $username, 'windows_fio' => $info['fio']]);

        return $next($request);
    }
}
