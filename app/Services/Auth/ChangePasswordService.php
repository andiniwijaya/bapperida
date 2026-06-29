<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Changes the authenticated user's password after verifying the current one.
 *
 * Business rules:
 * - Clears must_change_password after a successful change.
 * - Does not revoke existing tokens (unlike admin-initiated reset).
 *
 * Notification dispatch: passwordChanged after audit log.
 *
 * Related modules: User, Auth (AuthController::changePassword).
 */
class ChangePasswordService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * Verify current password and persist the new hashed password.
     *
     * @param  User  $user  Authenticated user.
     * @param  array{current_password: string, password: string}  $data
     * @return User Refreshed user model.
     *
     * @throws ValidationException When current_password does not match.
     */
    public function handle(
        User $user,
        array $data
    ): User {

        if (! Hash::check(
            $data['current_password'],
            $user->password
        )) {

            throw ValidationException::withMessages([
                'current_password' =>
                    'Password lama salah.',
            ]);
        }

        $user->update([
            'password' => $data['password'],
        ]);

        $user->must_change_password = false;
        $user->save();

        $this->activityLog->record(
            action: 'password_changed',
            module: 'auth',
            description: 'Pengguna mengubah password akun.',
            entity: $user,
            actor: $user,
        );

        $this->notificationService->passwordChanged($user);

        return $user->fresh();
    }
}
