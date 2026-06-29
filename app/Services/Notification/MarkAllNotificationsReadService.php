<?php

namespace App\Services\Notification;

use App\Models\User;

/**
 * Marks all unread notifications as read for a user.
 */
class MarkAllNotificationsReadService
{
    public function handle(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
