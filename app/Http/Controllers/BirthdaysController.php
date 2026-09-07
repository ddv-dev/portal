<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Worker;
use Carbon\Carbon;

class BirthdaysController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Получаем список всех месяцев, в которых есть дни рождения
            $months = Worker::selectRaw("
                DISTINCT EXTRACT(MONTH FROM birth_date) as month_number,
                CASE 
                    WHEN EXTRACT(MONTH FROM birth_date) = 1 THEN 'ЯНВ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 2 THEN 'ФЕВ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 3 THEN 'МАР'
                    WHEN EXTRACT(MONTH FROM birth_date) = 4 THEN 'АПР'
                    WHEN EXTRACT(MONTH FROM birth_date) = 5 THEN 'МАЙ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 6 THEN 'ИЮН'
                    WHEN EXTRACT(MONTH FROM birth_date) = 7 THEN 'ИЮЛ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 8 THEN 'АВГ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 9 THEN 'СЕН'
                    WHEN EXTRACT(MONTH FROM birth_date) = 10 THEN 'ОКТ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 11 THEN 'НОЯ'
                    WHEN EXTRACT(MONTH FROM birth_date) = 12 THEN 'ДЕК'
                END as month_name
            ")
            ->where('is_active', true)
            ->whereNotNull('birth_date')
            ->orderBy('month_number')
            ->get();

            // Преобразуем month_number в целые числа
            $months = $months->map(function($month) {
                $month->month_number = (int) $month->month_number;
                return $month;
            });

            // Логируем полученные месяцы
            Log::info('Months data:', $months->toArray());
            
            // Получаем текущий месяц
            $currentMonth = (int) Carbon::now()->month;
            
            // Определяем, есть ли поиск
            $hasSearch = $request->filled('search');
            
            // Определяем выбранный месяц
            if ($request->has('month') && $request->input('month') !== null && $request->input('month') !== '') {
                // Используем месяц из запроса
                $selectedMonth = (int) $request->input('month');
            } else {
                // По умолчанию используем текущий месяц
                $selectedMonth = $currentMonth;
            }
            
            // Проверяем, есть ли сотрудники в выбранном месяце
            $hasWorkersInSelectedMonth = $months->contains('month_number', $selectedMonth);
            
            // Если в выбранном месяце нет сотрудников, но есть в других месяцах
            if (!$hasWorkersInSelectedMonth && $months->isNotEmpty()) {
                // Находим ближайший месяц с сотрудниками
                $nearestMonth = $months->sortBy(function($month) use ($currentMonth) {
                    $diff = $month->month_number - $currentMonth;
                    if ($diff < 0) {
                        $diff += 12; // Для месяцев до текущего в следующем году
                    }
                    return $diff;
                })->first();
                
                if ($nearestMonth) {
                    $selectedMonth = $nearestMonth->month_number;
                }
            } elseif (!$hasWorkersInSelectedMonth && $months->isEmpty()) {
                // Если вообще нет сотрудников с днями рождения
                $selectedMonth = $currentMonth;
            }
            
            // Логируем выбранный месяц
            Log::info('Current month: ' . $currentMonth);
            Log::info('Selected month: ' . $selectedMonth);
            Log::info('Has search: ' . ($hasSearch ? 'yes' : 'no'));
            
            // Базовый запрос
            $query = Worker::query()
                ->with('depts')
                ->where('is_active', true)
                ->whereNotNull('birth_date');
            
            // Если есть поиск, ищем по всем месяцам
            if ($hasSearch) {
                $search = $request->input('search');
                $query->where('name_full', 'ILIKE', "%{$search}%");
                // Сортировка по месяцу и дню рождения
                $query->orderByRaw('EXTRACT(MONTH FROM birth_date)')
                      ->orderByRaw('EXTRACT(DAY FROM birth_date)');
            } else {
                // Если нет поиска, фильтруем по выбранному месяцу
                $query->whereRaw('EXTRACT(MONTH FROM birth_date) = ?', [$selectedMonth]);
                // Сортировка по дню рождения
                $query->orderByRaw('EXTRACT(DAY FROM birth_date)');
            }
            
            // Пагинация
            $perPage = 50;
            $workers = $query->paginate($perPage);
            
            // Логируем результаты
            Log::info('Workers count: ' . $workers->total());
            Log::info('SQL: ' . $query->toSql());
            Log::info('Bindings: ' . json_encode($query->getBindings()));

            return view('birthdays', compact('workers', 'months', 'selectedMonth', 'currentMonth', 'hasSearch'));

        } catch (\Exception $e) {
            Log::error('Error loading workers birthdays: '.$e->getMessage());
            Log::error('Error trace: '.$e->getTraceAsString());
            
            $workers = collect();
            $months = collect();
            $selectedMonth = (int) Carbon::now()->month;
            $currentMonth = (int) Carbon::now()->month;
            $hasSearch = false;
            
            return view('birthdays', compact('workers', 'months', 'selectedMonth', 'currentMonth', 'hasSearch'));
        }
    }
}