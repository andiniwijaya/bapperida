<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;

/**
 * Revokes the current Sanctum access token for logout.
 *
 * Related modules: User (HasApiTokens), Auth (AuthController::logout).
 */
class LogoutService
{
    public function __construct(private RecordActivityLogService $activityLog) {}

    /**
     * Delete the token used for the current API request.
     *
     * @param  User  $user  Authenticated user.
     */
    public function handle(User $user): void
    {
        $this->activityLog->record(
            action: 'logout',
            module: 'auth',
            description: sprintf('Pengguna %s logout.', $user->email),
            entity: $user,
            actor: $user,
        );

        $user->currentAccessToken()?->delete();
    }
}
