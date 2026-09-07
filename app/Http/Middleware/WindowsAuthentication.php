<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WindowsAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        // Проверяем сессию
        if (session()->has('windows_user')) {
            Log::info('User already in session', ['user' => session('windows_user')]);
            return $next($request);
        }

        // Получаем credentials
        $username = null;
        $password = null;
        
        // Проверяем PHP_AUTH_USER
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            Log::info('Got credentials from PHP_AUTH', ['user' => $username]);
        }
        
        // Проверяем заголовок
        if (!$username) {
            $auth = $request->header('Authorization') ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if ($auth && str_starts_with($auth, 'Basic ')) {
                $credentials = base64_decode(substr($auth, 6));
                $parts = explode(':', $credentials, 2);
                if (count($parts) === 2) {
                    [$username, $password] = $parts;
                    Log::info('Got credentials from Authorization header', ['user' => $username]);
                }
            }
        }
        
        // Если нет credentials - отправляем 401
        if (empty($username) || empty($password)) {
            Log::info('No credentials, sending 401 with WWW-Authenticate');
            
            // Важно: отправляем 401 с WWW-Authenticate для браузера
            return response('Unauthorized - Please provide credentials', 401)
                ->header('WWW-Authenticate', 'Basic realm="Windows Domain M01.DZM"')
                ->header('Content-Type', 'text/html; charset=utf-8');
        }
        
        // Очищаем имя пользователя
        $username = trim($username);
        $username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
        $username = preg_replace('/^.*\\\\/', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', $username);
        
        Log::info('Auth attempt', [
            'user' => $username,
            'password_length' => strlen($password),
        ]);
        
        // Проверка через wbinfo (или тестовая)
        $authSuccess = false;
        
        // Проверяем через wbinfo если доступен
        $wbinfoPath = trim(shell_exec('which wbinfo 2>/dev/null'));
        if (!empty($wbinfoPath)) {
            $cmd = "echo '" . addslashes($username) . "%" . addslashes($password) . "' | " . $wbinfoPath . " --authenticate 2>&1";
            exec($cmd, $output, $returnCode);
            
            Log::info('wbinfo result', [
                'user' => $username,
                'code' => $returnCode,
                'output' => $output
            ]);
            
            $authSuccess = ($returnCode === 0);
        } else {
            // Тестовый режим - принимаем любого пользователя
            Log::info('Using test mode - accepting any user', ['user' => $username]);
            $authSuccess = !empty($username) && !empty($password);
        }
        
        if ($authSuccess) {
            session(['windows_user' => $username]);
            $request->merge(['windows_user' => $username]);
            
            Log::info('✅ Auth success', ['user' => $username]);
            return $next($request);
        }
        
        Log::warning('❌ Auth failed', ['user' => $username]);
        return response('Invalid credentials', 401)
            ->header('WWW-Authenticate', 'Basic realm="Windows Domain M01.DZM"');
    }
}