<?php
// app/Http/Controllers/AttachmentController.php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Info;
use App\Models\Sop;
use App\Models\OrderStorage;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function download($model, $id, Attachment $attachment)
    {
        // Определяем модель по имени
        $modelMap = [
            'news' => News::class,
            'info' => Info::class,
            'sop' => Sop::class,
            'order' => OrderStorage::class,
        ];
        
        if (!isset($modelMap[$model])) {
            abort(404, 'Модель не найдена');
        }
        
        $modelClass = $modelMap[$model];
        $record = $modelClass::find($id);
        
        if (!$record) {
            abort(404, 'Запись не найдена');
        }
        
        // Проверяем, что вложение принадлежит этой записи
        if (!$record->attachments()->where('attachment_id', $attachment->id)->exists()) {
            abort(404, 'Вложение не принадлежит этой записи');
        }
        
        $filePath = storage_path('app/public/' . $attachment->path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Файл не найден');
        }
        
        return response()->download($filePath, $attachment->name);
    }
}