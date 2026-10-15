<?php
// app/Http/Controllers/Notifications/NotificationController.php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    protected function workerId(Request $request): ?int
    {
        return $request->session()->get('worker_id');
    }

    public function index(Request $request)
    {
        $workerId = $this->workerId($request);
        if (!$workerId) abort(403);

        $items = $this->notifications->forWorker($workerId, 30);
        return view('notifications.index', compact('items'));
    }

    public function unreadCount(Request $request)
    {
        $workerId = $this->workerId($request);
        if (!$workerId) return response()->json(['count' => 0]);

        return response()->json([
            'count' => $this->notifications->unreadCount($workerId),
        ]);
    }

    public function markRead(Request $request, int $id)
    {
        $workerId = $this->workerId($request);
        if (!$workerId) abort(403);

        $ok = $this->notifications->markAsRead($id, $workerId);
        return response()->json(['ok' => $ok]);
    }

    public function markAllRead(Request $request)
    {
        $workerId = $this->workerId($request);
        if (!$workerId) abort(403);

        $n = $this->notifications->markAllAsRead($workerId);
        return response()->json(['ok' => true, 'updated' => $n]);
    }
}