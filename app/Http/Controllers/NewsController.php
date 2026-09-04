<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Получаем количество записей на страницу
            $perPage = $request->input('perPage', session('perPage', 10));
            
            // Валидация разрешенных значений
            $perPage = in_array($perPage, [5, 10, 15, 20, 30, 50, 100]) ? $perPage : 10;
            
            // Сохраняем в сессию
            session(['perPage' => $perPage]);

            // Строим запрос
            $query = News::query()->with('attachments')->where('is_active', true);

            // Поиск по заголовку и описанию
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            }

            // Фильтр по году
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->input('year'));
            }

            // Сортировка
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            if (in_array($sortBy, ['title', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Получаем новости с пагинацией
            $news = $query->paginate($perPage)->appends([
                'perPage' => $perPage,
                'search' => $request->input('search'),
                'year' => $request->input('year'),
                'sort_by' => $request->input('sort_by'),
                'sort_order' => $request->input('sort_order'),
            ]);

            // Годы для фильтрации
            $years = News::where('is_active', true)
                ->selectRaw('EXTRACT(YEAR FROM created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

            // Общая статистика
            $totalCount = News::where('is_active', true)->count();

            Log::info('News page loaded', [
                'news_count' => $news->count(),
                'total_count' => $totalCount,
                'years_count' => $years->count(),
                'per_page' => $perPage,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading news: ' . $e->getMessage());
            
            $news = collect();
            $years = collect();
            $totalCount = 0;
            $perPage = 10;
        }

        return view('home', compact('news', 'years', 'totalCount', 'perPage'));
    }

    // Метод для поиска (если используется)
    public function search(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', session('perPage', 10));
            $perPage = in_array($perPage, [5, 10, 15, 20, 30, 50, 100]) ? $perPage : 10;

            $query = News::with('attachments')->where('is_active', true);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            }

            $news = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends(['search' => $search, 'perPage' => $perPage]);

            return view('home', compact('news', 'search'));

        } catch (\Exception $e) {
            Log::error('Error searching news: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Ошибка при поиске');
        }
    }
}