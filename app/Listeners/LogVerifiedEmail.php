<?php

namespace App\Listeners;

use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Events\Verified;

/**
 * Records email verification events in the audit trail.
 *
 * Audit trail: captures Fortify email verification for compliance.
 *
 * Notification dispatch: emailVerified after audit log.
 */
class LogVerifiedEmail
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof \App\Models\User) {
            return;
        }

        $this->activityLog->record(
            action: 'email_verified',
            module: 'auth',
            description: sprintf('Pengguna %s memverifikasi email.', $user->email),
            entity: $user,
            actor: $user,
        );

        $this->notificationService->emailVerified($user);
    }
}
