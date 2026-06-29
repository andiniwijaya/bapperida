<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * Fortify action for resetting a forgotten password via the web flow.
 *
 * Business rules:
 * - Clears must_change_password after reset (user chose the new password).
 * - Uses shared PasswordValidationRules from the application.
 *
 * Notification dispatch: passwordReset after audit log.
 *
 * Related modules: User, Fortify password reset routes, RecordActivityLogService.
 */
class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * Validate and persist a new password for the user.
     *
     * @param  User  $user  User completing password reset.
     * @param  array<string, string>  $input  Must include password and confirmation.
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => $input['password'],
            'must_change_password' => false,
        ])->save();

        $this->activityLog->record(
            action: 'password_reset',
            module: 'auth',
            description: sprintf('Pengguna %s mereset password via tautan email.', $user->email),
            entity: $user,
            actor: $user,
        );

        $this->notificationService->passwordReset($user);
    }
}
