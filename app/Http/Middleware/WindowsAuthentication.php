<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Worker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ActiveDirectoryService;
use App\Services\Redmine\RedmineUserService;
use App\Services\WorkerProvisioningService;

class WindowsAuthentication
{
    public function __construct(
        private ActiveDirectoryService $ad,
        private RedmineUserService $redmineUsers,
        private WorkerProvisioningService $workers,
    ) {}

    public function handle(Request $request, Closure $next)
    {

        if (session()->has('windows_user') && session()->has('worker_id')) {
            $workerAlive = Worker::whereKey(session('worker_id'))->exists();
            if ($workerAlive) {
                return $next($request);
            }
            session()->forget(['windows_user', 'worker_id']);
        }

        $username = $_SERVER['REMOTE_USER']
            ?? $_SERVER['GSSAPI_NAME']
            ?? $_SERVER['PHP_AUTH_USER']
            ?? null;

        if (empty($username)) {
            Log::warning('SSO: REMOTE_USER пуст');
            return response('Unauthorized', 401);
        }

        $username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
        $username = preg_replace('/^.*\\\\/', '', $username);
        $username = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', trim($username)));

        $cacheKey = "ad_info_{$username}";
        $info = Cache::get($cacheKey);

        if (!is_array($info)) {
            $fresh = $this->ad->getUserInfo($username);
            if (is_array($fresh) && !empty($fresh)) {
                Cache::put($cacheKey, $fresh, 3600);
                $info = $fresh;
            } else {
                $info = ['fio' => $username, 'error' => true];
                Log::warning('SSO: AD не вернул данные', ['user' => $username]);
            }
        }

        $fio = $info['fio'] ?? $username;

        $redmineUser = $this->redmineUsers->findOrCreateByLoginAd(
            $username,
            $fio,
            $info['email'] ?? null,
        );

        $worker = $this->workers->findOrCreateByLoginAd($username, $fio);

        if ($redmineUser && !$worker->redmine_user_id) {
            $worker->redmine_user_id = $redmineUser->id;
            $worker->save();
        } elseif ($redmineUser && $worker->redmine_user_id !== $redmineUser->id) {
            try {
                $worker->redmine_user_id = $redmineUser->id;
                $worker->save();
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('SSO: cannot bind redmine_user_id to worker', [
                    'worker_id'       => $worker->id,
                    'redmine_user_id' => $redmineUser->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        session([
            'windows_user'         => $username,
            'windows_fio'          => $fio,
            'windows_department'   => $info['department'] ?? null,
            'windows_email'        => $info['email']      ?? null,
            'windows_title'        => $info['title']      ?? null,

            'redmine_user_id'      => $redmineUser?->id,
            'redmine_login'        => $redmineUser?->login,
            'redmine_fio'          => $redmineUser?->full_name,

            'worker_id'            => $worker->id,
            'worker_fio'           => $worker->name_full,
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
