<?php

namespace App\Http\Controllers\UserApi\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Notification\IndexNotificationsRequest;
use App\Http\Resources\User\NotificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(IndexNotificationsRequest $request): JsonResponse
    {
        $notifications = Auth::guard('api-user')->user()
            ->notifications()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->success([
            'notifications' => $notifications->getCollection()
                ->map(fn ($notification) => (new NotificationResource($notification))->resolve($request))
                ->values(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ], 'messages.notification.retrieved');
    }

    public function unreadCount(): JsonResponse
    {
        return $this->success([
            'unread_count' => Auth::guard('api-user')->user()->unreadNotifications()->count(),
        ], 'messages.notification.unread_count');
    }

    public function markAsRead(string $notification): JsonResponse
    {
        $userNotification = Auth::guard('api-user')->user()
            ->notifications()
            ->findOrFail($notification);
        $userNotification->markAsRead();

        return $this->success(
            (new NotificationResource($userNotification->fresh()))->resolve(request()),
            'messages.notification.marked_read'
        );
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::guard('api-user')->user();
        $unread = $user->unreadNotifications();
        $updatedCount = $unread->count();
        $unread->update(['read_at' => now(), 'updated_at' => now()]);

        return $this->success([
            'updated_count' => $updatedCount,
        ], 'messages.notification.marked_all_read');
    }
}
