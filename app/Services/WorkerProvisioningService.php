<?php

namespace App\Services;

use App\Models\Worker;
use Illuminate\Support\Facades\Log;

class WorkerProvisioningService
{
    public function findOrCreateByLoginAd(string $loginAd, ?string $fio = null): ?Worker
    {
        $fio = $fio ?: $loginAd;

        $worker = Worker::where('login_ad', $loginAd)->first();
        if ($worker) {
            return $worker;
        }

        if ($fio !== $loginAd) {
            $worker = $this->attachLoginToExisting($loginAd, $fio);
            if ($worker) {
                return $worker;
            }
        }

        return $this->create($loginAd, $fio);
    }

    private function attachLoginToExisting(string $loginAd, string $fio): ?Worker
    {
        $candidates = Worker::whereNull('login_ad')
            ->whereRaw('TRIM(name_full) = ?', [trim($fio)])
            ->limit(2)
            ->get();

        if ($candidates->count() !== 1) {
            if ($candidates->count() > 1) {
                Log::warning('Worker attach ambiguous — multiple candidates', [
                    'login_ad' => $loginAd,
                    'fio'      => $fio,
                ]);
            }
            return null;
        }

        $worker = $candidates->first();

        try {
            $affected = Worker::where('id', $worker->id)
                ->whereNull('login_ad')
                ->update(['login_ad' => $loginAd]);

            if ($affected === 0) {
                // Кто-то успел привязать раньше — перезапрашиваем
                return Worker::where('login_ad', $loginAd)->first();
            }

            $worker->refresh();

            Log::info('Worker attached to AD login', [
                'worker_id' => $worker->id,
                'login_ad'  => $loginAd,
                'name_full' => $fio,
            ]);

            return $worker;
        } catch (\Throwable $e) {
            Log::warning('Worker attach failed, retrying by login_ad', [
                'login_ad' => $loginAd,
                'error'    => $e->getMessage(),
            ]);

            return Worker::where('login_ad', $loginAd)->first();
        }
    }

    private function create(string $loginAd, string $fio): ?Worker
    {
        try {
            $worker = Worker::create([
                'login_ad'  => $loginAd,
                'name_full' => $fio,
                'is_active' => true,
            ]);

            Log::info('Worker created', [
                'worker_id' => $worker->id,
                'login_ad'  => $loginAd,
            ]);

            return $worker;
        } catch (\Throwable $e) {
            $worker = Worker::where('login_ad', $loginAd)->first();

            if (! $worker) {
                Log::error('Worker creation failed', [
                    'login_ad' => $loginAd,
                    'error'    => $e->getMessage(),
                ]);
            }

            return $worker;
        }
    }
}