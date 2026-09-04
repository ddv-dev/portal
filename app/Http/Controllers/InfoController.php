<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Info;
use App\Models\InfoType;
use Illuminate\Support\Facades\Log;

class InfoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Info::query()->with('attachments');

            // Поиск по заголовку
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%");
                });
            }

            // Фильтр по типу
            if ($request->filled('type')) {
                $query->where('type_id', $request->input('type'));
            }

            // Сортировка
            $sortBy = $request->input('sort_by', 'order');
            $sortOrder = $request->input('sort_order', 'asc');
            
            if (in_array($sortBy, ['title', 'created_at', 'order'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $infos = $query->paginate(15);

            // Все типы информации с сортировкой по order_menu
            $infoTypes = InfoType::ordered()->get();
            
            // Типы с количеством активных записей
            $infoTypesWithCount = $infoTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'count' => $type->infos()->count(), // Исправлено: infos() вместо info()
                    'order_menu' => $type->order_menu,
                ];
            });
            
            // Простой список id => name
            $infoTypesList = $infoTypes->pluck('name', 'id')->toArray();

            // Годы для фильтрации
            $years = Info::selectRaw('EXTRACT(YEAR FROM created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

            // Общая статистика
            $totalCount = Info::query()->count();

        } catch (\Exception $e) {
            Log::error('Error loading info: ' . $e->getMessage());
            $infos = collect();
            $infoTypes = collect();
            $infoTypesWithCount = collect();
            $infoTypesList = [];
            $years = collect();
            $totalCount = 0;
        }

        return view('info', compact(
            'infos',
            'infoTypes',
            'infoTypesWithCount',
            'infoTypesList',
        ));
    }
}