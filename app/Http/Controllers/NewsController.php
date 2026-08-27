<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        // Используем input() вместо get()
        $perPage = $request->input('perPage', session('perPage', 1));
        
        // Валидация разрешенных значений
        $perPage = in_array($perPage, [1, 20, 50, 100]) ? $perPage : 1;
        
        // Сохраняем в сессию
        session(['perPage' => $perPage]);

        // Получаем новости с пагинацией
        $news = News::where('is_active', true)
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        return view('home', compact('news'));
    }
}