<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;

/**
 * Initiates admin-driven password reset via Fortify reset link email.
 *
 * Business rules:
 * - Revokes all Sanctum tokens after reset initiation.
 * - Sets must_change_password to true until the user completes the email flow.
 * - Does not generate or email a temporary password.
 *
 * Audit trail: records password reset by acting admin on target user.
 *
 * Notification dispatch: passwordSetupInvitation after audit log.
 *
 * Related modules: User, UserController, Fortify password reset routes.
 */
class ResetUserPasswordService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
        private IssuePasswordSetupLinkService $passwordSetupLinkService,
    ) {}

    /**
     * Issue a new password-setup link for the target user.
     *
     * @param  User  $user  Target user.
     * @return array{user: User}
     */
    public function handle(User $user): array
    {
        UserManagementGuard::ensureAdminManagesStaffOnly($user);

        $this->passwordSetupLinkService->assignUnusablePassword($user);

        $user->tokens()->delete();

        $user = $user->fresh();

        $passwordSetupUrl = $this->passwordSetupLinkService->createResetUrl($user);

        $this->activityLog->record(
            action: 'password_reset',
            module: 'user',
            description: sprintf('Password pengguna %s berhasil direset.', $user->email),
            entity: $user,
            actor: Auth::user(),
        );

        $this->notificationService->passwordSetupInvitation(
            $user,
            $passwordSetupUrl,
            'reset',
        );

        return [
            'user' => $user,
        ];
    }
}
