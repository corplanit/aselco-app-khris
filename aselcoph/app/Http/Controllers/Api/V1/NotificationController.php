<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppNotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = min(50, max(5, (int) $request->query('per_page', 30)));

        $items = AppNotification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($perPage)
            ->get();

        $unreadCount = AppNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => AppNotificationResource::collection($items)->resolve(),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => (new AppNotificationResource($notification))->resolve(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $updated = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'updated' => $updated,
        ]);
    }
}
