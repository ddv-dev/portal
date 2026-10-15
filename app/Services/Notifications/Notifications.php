<?php
// app/Services/Notifications/NotificationService.php

namespace App\Services\Notifications;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notify(
        int $workerId,
        string $type,
        array $data = [],
        ?int $actorWorkerId = null,
        ?string $dedupKey = null,
    ): ?Notification {
        if ($actorWorkerId !== null && $actorWorkerId === $workerId) {
            return null; // автору — не шлём
        }

        if ($dedupKey !== null &&
            Notification::where('worker_id', $workerId)
                ->where('dedup_key', $dedupKey)
                ->exists()
        ) {
            return null;
        }

        try {
            return Notification::create([
                'worker_id'       => $workerId,
                'actor_worker_id' => $actorWorkerId,
                'type'            => $type,
                'notifiable_type' => $data['notifiable_type'] ?? null,
                'notifiable_id'   => $data['notifiable_id']   ?? null,
                'title'           => $data['title']           ?? null,
                'body'            => $data['body']            ?? null,
                'payload'         => $data['payload']         ?? null,
                'url'             => $data['url']             ?? null,
                'dedup_key'       => $dedupKey,
                'created_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Notification create failed', [
                'worker_id' => $workerId,
                'type'      => $type,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function notifyMany(
        array $workerIds,
        string $type,
        array $data = [],
        ?int $actorWorkerId = null,
        ?string $dedupKeyBase = null,
    ): Collection {
        $created = collect();
        foreach (array_unique(array_map('intval', $workerIds)) as $wid) {
            $dedup = $dedupKeyBase !== null ? "{$dedupKeyBase}:{$wid}" : null;
            $n = $this->notify($wid, $type, $data, $actorWorkerId, $dedup);
            if ($n) $created->push($n);
        }
        return $created;
    }


    public function forWorker(int $workerId, int $perPage = 30)
    {
        return Notification::forWorker($workerId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function unreadCount(int $workerId): int
    {
        return Notification::forWorker($workerId)->unread()->count();
    }

    public function markAsRead(int $notificationId, int $workerId): bool
    {
        $n = Notification::forWorker($workerId)->find($notificationId);
        if (!$n) return false;
        $n->markAsRead();
        return true;
    }

    public function markAllAsRead(int $workerId): int
    {
        return Notification::forWorker($workerId)->unread()
            ->update(['read_at' => now()]);
    }
}