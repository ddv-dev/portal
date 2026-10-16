<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profile) {}

    public function updatePhone(Request $request)
    {
        Log::info('updatePhone IN', [
            'all'        => $request->all(),
            'has_city'   => $request->has('city'),
            'has_mobile' => $request->has('mobile'),
            'input_city' => $request->input('city'),
            'input_mobile' => $request->input('mobile'),
            'worker_id_session' => session('worker_id'),
        ]);

        $data = $request->validate([
            'city'   => ['sometimes', 'nullable', 'string', 'regex:/^\d{10}$/'],
            'mobile' => ['sometimes', 'nullable', 'string', 'regex:/^\d{4,6}$/'],
        ], [
            'city.regex'   => 'Городской номер должен содержать ровно 11 цифр.',
            'mobile.regex' => 'Внутренний номер должен содержать от 4 до 6 цифр.',
        ]);

        Log::info('updatePhone VALIDATED', ['data' => $data]);

        $worker = $this->profile->currentWorker();
        Log::info('updatePhone WORKER', ['worker_id' => $worker?->id]);

        if (!$worker) {
            return back()->withErrors(['phone' => 'Сотрудник не найден.']);
        }

        try {
            if ($request->has('city')) {
                $this->profile->syncCityPhone($worker, $data['city'] ?? '');
            }
            if ($request->has('mobile')) {
                $this->profile->syncMobilePhone($worker, $data['mobile'] ?? '');
            }
        } catch (\Throwable $e) {
            Log::error('updatePhone ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['phone' => 'Не удалось сохранить телефон.']);
        }

        Log::info('updatePhone DONE', [
            'city_after'   => optional($worker->fresh()->cityPhone()->first())->phone_number,
            'mobile_after' => optional($worker->fresh()->mobilePhone()->first())->phone_number,
        ]);

        return back()->with('status', 'Телефон обновлён');
    }
}
