<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\OrderStorage;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = OrderStorage::query()->with('attachments');
            
            // Фильтр по статусу
            if ($request->has('status')) {
                if ($request->input('status') === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->input('status') === 'inactive') {
                    $query->where('is_active', false);
                }
            }
            
            // Поиск по номеру, названию, описанию
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                      ->orWhere('number', 'ILIKE', "%{$search}%")
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
            
            if (in_array($sortBy, ['title', 'number', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // Пагинация
            $orders = $query->paginate(15);
            
            // Статистика для меню
            $activeCount = OrderStorage::where('is_active', true)->count();
            $inactiveCount = OrderStorage::where('is_active', false)->count();
            $totalCount = OrderStorage::count();
            
            // Доступные годы
            $years = OrderStorage::selectRaw('EXTRACT(YEAR FROM created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
            
        } catch (\Exception $e) {
            Log::error('Error loading orders: ' . $e->getMessage());
            $orders = collect();
            $activeCount = 0;
            $inactiveCount = 0;
            $totalCount = 0;
            $years = collect();
        }

        return view('orders', compact(
            'orders',
            'activeCount',
            'inactiveCount',
            'totalCount',
            'years'
        ));
    }
}