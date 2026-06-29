<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates admin or staff users with Fortify password-setup onboarding.
 *
 * Business rules:
 * - Super Admin may create admin or staff; Admin may create staff only.
 * - User is active immediately with email_verified_at set and must_change_password true.
 * - A random password hash is stored but never exposed; user sets password via email link.
 *
 * Audit trail: records user creation with new user entity reference.
 * Notification dispatch: userCreated after audit log.
 */
class StoreUserService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
        private IssuePasswordSetupLinkService $passwordSetupLinkService,
    ) {}

    /**
     * @param  array{name: string, username: string, email: string, role: string, department_id: int}  $data
     * @return array{user: User}
     */
    public function handle(array $data): array
    {
        UserManagementGuard::ensureAdminMayAssignRole($data['role']);

        return DB::transaction(function () use ($data) {
            $randomPassword = Str::password(
                length: 64,
                letters: true,
                numbers: true,
                symbols: true,
                spaces: false,
            );

            $user = User::create([
                'name' => $data['name'],
                'username' => strtolower($data['username']),
                'email' => strtolower($data['email']),
                'password' => Hash::make($randomPassword),
                'department_id' => $data['department_id'],
            ]);

            $user->role = $data['role'];
            $user->status = 'active';
            $user->must_change_password = true;
            $user->email_verified_at = now();
            $user->save();

            $passwordSetupUrl = $this->passwordSetupLinkService->createResetUrl($user);

            $this->activityLog->record(
                action: 'user_created',
                module: 'user',
                description: sprintf('Pengguna baru %s berhasil dibuat.', $user->email),
                entity: $user,
            );

            $this->notificationService->userCreated($user, $passwordSetupUrl);

            return [
                'user' => $user,
            ];
        });
    }
}
