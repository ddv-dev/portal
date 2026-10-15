<?php

namespace App\Observers;

use App\Models\News;
use App\Services\Notifications\NewsNotifier;
use Illuminate\Support\Facades\Log;

class NewsObserver
{
    public function created(News $news): void
    {
        // --- Вложения из сессии (если используешь этот механизм) ---
        $uploadedFiles = session('news_attachments') ?? [];
        if (!empty($uploadedFiles)) {
            $news->createAttachmentsFromUploads($uploadedFiles);
            session()->forget('news_attachments');
        }

        // --- Уведомления всем работникам ---
        try {
            $actorWorkerId = session('worker_id'); // может быть null — это ок
            app(NewsNotifier::class)->onCreated($news, $actorWorkerId);
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