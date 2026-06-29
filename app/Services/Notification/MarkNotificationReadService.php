<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Marks a single notification as read for the owning user.
 */
class MarkNotificationReadService
{
    public function handle(User $user, DatabaseNotification $notification): DatabaseNotification
    {
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== $user->getMorphClass()) {
            abort(403, 'Unauthorized notification access.');
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return $notification->fresh();
    }
}
