<?php

namespace App\Services\Notifications;

use App\Models\News;
use App\Models\Worker;
use App\Notifications\Types\NotificationType;

class NewsNotifier
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function onCreated(News $news, ?int $actorWorkerId = null): void
    {
        // все активные работники; если нужно "все вообще" — убери ->where('is_active', true)
        $workerIds = Worker::query()
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        if (empty($workerIds)) return;

        $this->notifications->notifyMany(
            $workerIds,
            NotificationType::NEWS_CREATED,
            [
                'notifiable_type' => News::class,
                'notifiable_id'   => $news->id,
                'title'           => 'Новая новость',
                'body'            => (string) $news->title,
                'url'             => route('news.show', $news->id), // подставь свой роут
                'payload'         => ['news_id' => $news->id],
            ],
            $actorWorkerId,
            "news_created:{$news->id}",
        );
    }
}