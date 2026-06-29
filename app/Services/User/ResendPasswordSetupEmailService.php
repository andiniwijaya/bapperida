<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Resends the password-setup email for users who have not completed onboarding.
 *
 * Business rules:
 * - Only available when must_change_password is true.
 * - Does not create a new user or registration request.
 * - Issues a fresh Fortify password reset token for the existing account.
 */
class ResendPasswordSetupEmailService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
        private IssuePasswordSetupLinkService $passwordSetupLinkService,
    ) {}

    /**
     * Resend password-setup email to a user pending onboarding.
     *
     * @param  User  $user  Target user.
     * @return array{user: User}
     *
     * @throws ValidationException When the user has already set their password.
     */
    public function handle(User $user): array
    {
        UserManagementGuard::ensureAdminManagesStaffOnly($user);

        if (! $user->must_change_password) {
            throw ValidationException::withMessages([
                'user' => 'Pengguna sudah mengatur kata sandi.',
            ]);
        }

        $passwordSetupUrl = $this->passwordSetupLinkService->createResetUrl($user);

        $this->activityLog->record(
            action: 'password_setup_email_resent',
            module: 'user',
            description: sprintf('Email atur kata sandi dikirim ulang untuk %s.', $user->email),
            entity: $user,
            actor: Auth::user(),
        );

        $this->notificationService->passwordSetupInvitation(
            $user,
            $passwordSetupUrl,
            'resent',
        );

        return [
            'user' => $user->fresh(),
        ];
    }
}
