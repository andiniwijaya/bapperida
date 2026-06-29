<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Validation\ValidationException;

/**
 * Updates a user's account status (pending, active, rejected).
 *
 * Business rules:
 * - Super Admin status cannot be changed.
 *
 * Audit trail: records status change with new status in properties.
 *
 * Notification dispatch: userStatusChanged after audit log.
 *
 * Related modules: User, UserController, Auth (AccountStatusService).
 */
class ChangeUserStatusService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * Persist a new status value on the user.
     *
     * @param  User  $user  Target user.
     * @param  string  $status  One of pending, active, rejected.
     * @return User Refreshed user model.
     *
     * @throws ValidationException When targeting a superadmin account.
     */
    public function handle(User $user, string $status): User
    {
        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'status' => 'Status Super Admin tidak dapat diubah.',
            ]);
        }

        $user->status = $status;
        $user->save();

        $user = $user->fresh();

        $this->activityLog->record(
            action: 'user_status_changed',
            module: 'user',
            description: sprintf('Status pengguna %s diubah menjadi %s.', $user->email, $status),
            entity: $user,
            properties: ['status' => $status],
        );

        $this->notificationService->userStatusChanged($user, $status);

        return $user;
    }
}
