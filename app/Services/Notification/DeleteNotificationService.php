<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Deletes a notification owned by the authenticated user.
 */
class DeleteNotificationService
{
    public function handle(User $user, DatabaseNotification $notification): void
    {
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== $user->getMorphClass()) {
            abort(403, 'Unauthorized notification access.');
        }

        $notification->delete();
    }
}
