<?php

namespace App\Observers;

use App\Models\News;
use Filament\Notifications\Notification;

class NewsObserver
{
    public function created(News $news)
    {
        // Получаем загруженные файлы из сессии или запроса
        $uploadedFiles = session('news_attachments') ?? [];
        
        if (!empty($uploadedFiles)) {
            $news->createAttachmentsFromUploads($uploadedFiles);
            session()->forget('news_attachments');
        }
    }

    public function updated(News $news)
    {
        // Обработка обновления файлов
        $uploadedFiles = session('news_attachments') ?? [];
        
        if (!empty($uploadedFiles)) {
            $news->createAttachmentsFromUploads($uploadedFiles);
            session()->forget('news_attachments');
        }
    }
}