<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Notification\FilterNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\Notification\DeleteNotificationService;
use App\Services\Notification\GetUnreadNotificationCountService;
use App\Services\Notification\ListNotificationService;
use App\Services\Notification\MarkAllNotificationsReadService;
use App\Services\Notification\MarkNotificationReadService;
use Illuminate\Http\JsonResponse;

/**
 * User notification API (communication layer).
 *
 * Business rules:
 * - Users may only access their own notifications (scoped queries + IDOR checks in services).
 */
class NotificationController extends ApiController
{
    public function __construct(
        protected ListNotificationService $listService,
        protected GetUnreadNotificationCountService $unreadCountService,
        protected MarkNotificationReadService $markReadService,
        protected MarkAllNotificationsReadService $markAllReadService,
        protected DeleteNotificationService $deleteService,
    ) {}

    public function index(FilterNotificationRequest $request): JsonResponse
    {
        $notifications = $this->listService->handle($request->user(), [
            'unread_only' => $request->boolean('unread_only'),
            'per_page' => $request->integer('per_page', 15),
        ]);

        return $this->success([
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ], 'Notifications retrieved successfully.');
    }

    public function unreadCount(): JsonResponse
    {
        $count = $this->unreadCountService->handle(request()->user());

        return $this->success(['unread_count' => $count], 'Unread notification count retrieved.');
    }

    public function markAsRead(string $notificationId): JsonResponse
    {
        $notification = request()->user()->notifications()->whereKey($notificationId)->firstOrFail();

        $notification = $this->markReadService->handle(request()->user(), $notification);

        return $this->success(
            new NotificationResource($notification),
            'Notification marked as read.'
        );
    }

    public function markAllAsRead(): JsonResponse
    {
        $count = $this->markAllReadService->handle(request()->user());

        return $this->success(['marked_count' => $count], 'All notifications marked as read.');
    }

    public function destroy(string $notificationId): JsonResponse
    {
        $notification = request()->user()->notifications()->whereKey($notificationId)->firstOrFail();

        $this->deleteService->handle(request()->user(), $notification);

        return $this->success(null, 'Notification deleted.');
    }
}
