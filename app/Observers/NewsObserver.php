<?php

namespace App\Observers;

use App\Models\News;
use Filament\Notifications\Notification;

class NewsObserver
{
    public function created(News $news)
    {
<<<<<<< HEAD
=======
        // Получаем загруженные файлы из сессии или запроса
>>>>>>> 527adbd3def5b5a05c6b88b3685f79fb6cd11d58
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