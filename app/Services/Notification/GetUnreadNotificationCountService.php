<?php

namespace App\Services\Notification;

use App\Models\User;

/**
 * Returns unread notification count for dashboard badge.
 */
class GetUnreadNotificationCountService
{
    public function handle(User $user): int
    {
        return $user->unreadNotifications()->count();
    }
}
