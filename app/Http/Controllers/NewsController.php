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
    
   public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        // Поиск новостей по заголовку и описанию
        $news = News::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            })
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($news) {
                return [
                    'id' => $news->id,
                    'title' => $news->title,
                    'description' => Str::limit($news->description, 150),
                    'created_at' => $news->created_at ? $news->created_at->format('d.m.Y') : null,
                    'images' => $news->getImagesAttribute()->take(3)->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => Storage::disk('public')->url($image->path),
                            'name' => $image->name,
                        ];
                    }),
                    'files' => $news->getFilesAttribute()->take(3)->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'name' => $file->name,
                            'url' => Storage::disk('public')->url($file->path),
                        ];
                    }),
                    'attachments_count' => $news->attachments->count(),
                ];
            });

        return response()->json($news);
    }
}