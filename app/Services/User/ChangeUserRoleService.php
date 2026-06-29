<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Validation\ValidationException;

/**
 * Updates a user's role between admin and staff.
 *
 * Business rules:
 * - Super Admin role cannot be changed or assigned via this service.
 *
 * Audit trail: records role change with new role in properties.
 *
 * Notification dispatch: userRoleChanged after audit log.
 *
 * Related modules: User, UserController, RoleMiddleware.
 */
class ChangeUserRoleService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * Persist a new role value on the user.
     *
     * @param  User  $user  Target user.
     * @param  string  $role  admin or staff.
     * @return User Refreshed user model.
     *
     * @throws ValidationException When targeting a superadmin account.
     */
    public function handle(User $user, string $role): User
    {
        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role' => 'Role Super Admin tidak dapat diubah.',
            ]);
        }

        $user->role = $role;
        $user->save();

        $user = $user->fresh();

        $this->activityLog->record(
            action: 'user_role_changed',
            module: 'user',
            description: sprintf('Role pengguna %s diubah menjadi %s.', $user->email, $role),
            entity: $user,
            properties: ['role' => $role],
        );

        $this->notificationService->userRoleChanged($user, $role);

        return $user;
    }
}
