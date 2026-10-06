<?php

namespace App\Services;

use App\Models\Phone;
use App\Models\Worker;
use Illuminate\Support\Facades\Log;

class ProfileService
{
    public function currentWorker(): ?Worker
    {
        if ($id = session('worker_id')) {
            $worker = Worker::find($id);
            if ($worker) {
                return $worker;
            }
            // Сессия ссылается на удалённого работника — забываем
            session()->forget('worker_id');
        }

        // Fallback: ищем по логину SSO и записываем актуальный id
        if ($login = session('windows_user')) {
            $worker = Worker::where('login_ad', $login)->first();
            if ($worker) {
                session(['worker_id' => $worker->id]);
                return $worker;
            }
        }

        return null;
    }

    public function updatePhones(Worker $worker, ?string $city, ?string $mobile): void
    {
        $this->syncPhone($worker, $city   ?? '', true);
        $this->syncPhone($worker, $mobile ?? '', false);
    }

    protected function syncPhone(Worker $worker, string $number, bool $isCity): void
    {
        $number = trim($number);

        Log::info('syncPhone IN', [
            'worker_id' => $worker->id,
            'number'    => $number,
            'isCity'    => $isCity,
        ]);

        $existing = $worker->phones()
            ->where('phone.is_city_number', $isCity)
            ->first();

        Log::info('syncPhone EXISTING', [
            'existing_id'     => $existing?->id,
            'existing_number' => $existing?->phone_number,
        ]);

        if ($number === '') {
            if ($existing) {
                $worker->phones()->detach($existing->id);
                $existing->delete();
            }
            return;
        }

        if ($existing) {
            $existing->update(['phone_number' => $number]);
            Log::info('syncPhone UPDATED', ['id' => $existing->id, 'number' => $number]);
            return;
        }

        $phone = Phone::create([
            'phone_number'   => $number,
            'is_city_number' => $isCity,
        ]);

        Log::info('syncPhone CREATED', ['id' => $phone->id, 'number' => $number]);

        $worker->phones()->attach($phone->id);
    }

    public function syncCityPhone(Worker $worker, string $number): void
    {
        $this->syncPhone($worker, $number, true);
    }

    public function syncMobilePhone(Worker $worker, string $number): void
    {
        $this->syncPhone($worker, $number, false);
    }

    public function headerData(): array
    {
        $worker = $this->currentWorker();

        if (!$worker) {
            return [
                'name'       => session('windows_fio') ?? 'Гость',
                'department' => session('windows_department'),
                'position'   => session('windows_title'),
                'email'      => session('windows_email'),
                'username'   => session('windows_user'),
                'city_phone'   => '',
                'mobile_phone' => '',
            ];
        }

        return [
            'name'       => $worker->name_full,
            'department' => $worker->department ?? session('windows_department'),
            'position'   => $worker->position   ?? session('windows_title'),
            'email'      => session('windows_email'),
            'username'   => session('windows_user'),
            'city_phone'   => optional($worker->cityPhone()->first())->phone_number   ?? '',
            'mobile_phone' => optional($worker->mobilePhone()->first())->phone_number ?? '',
        ];
    }
}
