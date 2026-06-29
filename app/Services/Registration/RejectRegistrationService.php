<?php

namespace App\Services\Registration;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Rejects pending self-registration requests.
 *
 * Audit trail: records superadmin rejection with reason.
 *
 * Notification dispatch: registrationRejected after audit log.
 */
class RejectRegistrationService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    public function handle(
        RegistrationRequest $request,
        int $superAdminId,
        string $reason
    ): RegistrationRequest {

        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'registration_request' => 'Permintaan registrasi sudah diproses.',
            ]);
        }

        return DB::transaction(function () use (
            $request,
            $superAdminId,
            $reason
        ) {

            $request->update([
                'status' => 'rejected',
                'approved_by' => $superAdminId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $user = $request->user;
            $user->status = 'rejected';
            $user->save();

            $actor = User::query()->findOrFail($superAdminId);

            $this->activityLog->record(
                action: 'registration_rejected',
                module: 'auth',
                description: sprintf('Superadmin menolak registrasi pengguna %s.', $user->email),
                entity: $user,
                actor: $actor,
                properties: [
                    'registration_request_id' => $request->id,
                    'reason' => $reason,
                ],
            );

            $this->notificationService->registrationRejected($user, $reason);

            return $request->fresh();
        });
    }
}
