<?php

namespace App\Http\Controllers;

use App\Models\DutyRole;
use App\Models\DutyWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DutyController extends Controller
{
    /**
     * Дежурства за месяц.
     *
     * GET /duty?month=11&year=2026
     * Если не переданы — берём текущий месяц.
     */
    public function index(Request $request): View
    {
        // ---- 1. Определяем месяц/год ----
        $now   = now();
        $year  = (int) $request->integer('year', $now->year);
        $month = (int) $request->integer('month', $now->month);

        // защита от мусора: 1..12
        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = $now->year;
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // ---- 2. Роли (колонки таблицы) ----
        $roles = DutyRole::query()
            ->orderBy('id')            // порядок колонок = порядок ролей
            ->get(['id', 'name']);

        $rolesByIdea = $roles->keyBy('id');

        // ---- 3. Все дежурства за месяц ----
        $rows = DutyWorker::query()
            ->with('role:id,name')
            ->whereBetween('duty_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('duty_date')
            ->orderBy('duty_role_id')
            ->get();

        // ---- 4. Группируем по дате и раскладываем по ролям ----
        // Структура: [ '2026-11-01' => [ role_id => [ DutyWorker, ... ], ... ], ... ]
        $byDate = $rows
            ->groupBy(fn(DutyWorker $d) => $d->duty_date->toDateString())
            ->map(function ($dayItems) {
                return $dayItems->groupBy('duty_role_id')->map(function ($roleItems) {
                    // СОРТИРОВКА ВНУТРИ ЯЧЕЙКИ:
                    // Сортируем по полю 'number' по возрастанию.
                    // Если number числовой — работает как числа (1, 2, 10).
                    // Если строковый — используется естественная сортировка (natcasesort).
                    return $roleItems->sortBy(function ($item) {
                        return (int) $item->number; // Приводим к int для правильной сортировки (чтобы 10 было после 2, а не после 1)
                    })->values();
                });
            });

        // ---- 5. Формируем дни месяца (включая пустые) ----
        $days = [];
        $cursor = $startOfMonth->copy();

        while ($cursor->lte($endOfMonth)) {
            $dateKey = $cursor->toDateString();

            $cells = [];
            foreach ($roles as $role) {
                $items = $byDate->get($dateKey)?->get($role->id) ?? collect();

                $cells[$role->id] = $items
                    ->map(fn(DutyWorker $d) => [
                        'id'       => $d->id,
                        'fullname' => $d->fullname,
                        'number'   => $d->number,
                    ])
                    ->values()
                    ->all();
            }

            $days[] = [
                'date'          => $dateKey,
                'day'           => $cursor->day,                        // 1..31
                'weekday_ru'    => $this->weekdayRu($cursor),           // Понедельник / ...
                'is_weekend'    => $cursor->isWeekend(),                // для подсветки
                'cells'         => $cells,                              // [ role_id => [ {fullname, number}, ... ] ]
            ];

            $cursor->addDay();
        }

        return view('duty', [
            'year'        => $year,
            'month'       => $month,
            'month_label' => $startOfMonth->translatedFormat('F Y'), // «Ноябрь 2026»
            'roles'       => $roles,                                 // для <thead>
            'days'        => $days,                                  // для <tbody>
        ]);
    }

    /** Русское название дня недели */
    private function weekdayRu(Carbon $date): string
    {
        return match ($date->isoWeekday()) {
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье',
        };
    }
}
