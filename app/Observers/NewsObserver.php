<?php

namespace App\Observers;

use App\Models\News;
use App\Services\Notifications\NewsNotifier;
use Illuminate\Support\Facades\Log;

class NewsObserver
{
    public function created(News $news): void
    {
        // Привязка загруженных файлов (если используется сессия)
        $uploadedFiles = session('news_attachments') ?? [];
        if (!empty($uploadedFiles)) {
            $news->createAttachmentsFromUploads($uploadedFiles);
            session()->forget('news_attachments');
        }

        try {
            app(NewsNotifier::class)->onCreated($news, session('worker_id'));
        } catch (\Throwable $e) {
            Log::error('NewsObserver: onCreated failed', [
                'news_id' => $news->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function updated(News $news): void
    {
        $uploadedFiles = session('news_attachments') ?? [];
        if (!empty($uploadedFiles)) {
            $news->createAttachmentsFromUploads($uploadedFiles);
            session()->forget('news_attachments');
        }
    }
}