<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WindowsAuthentication
{
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

        // 3. Нормализуем DOMAIN\user или user@DOMAIN → user
        $username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
        $username = preg_replace('/^.*\\\\/', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', trim($username));

        session(['windows_user' => $username]);
        $request->merge(['windows_user' => $username]);
        $request->attributes->set('windows_user', $username);

        Log::info('SSO auth success', ['user' => $username]);

        return $next($request);
    }
}