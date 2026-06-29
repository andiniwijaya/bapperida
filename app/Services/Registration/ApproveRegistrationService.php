<?php

namespace App\Services\Registration;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Approves pending self-registration requests.
 *
 * Audit trail: records superadmin approval with target user reference.
 *
 * Notification dispatch: registrationApproved after audit log.
 */
class ApproveRegistrationService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    public function handle(RegistrationRequest $request, int $superAdminId): RegistrationRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'registration_request' => 'Permintaan registrasi sudah diproses.',
            ]);
        }

        return DB::transaction(function () use ($request, $superAdminId) {

            $request->update([
                'status' => 'approved',
                'approved_by' => $superAdminId,
                'approved_at' => now(),
            ]);

            $user = $request->user;
            $user->status = 'active';
            $user->save();

            $actor = User::query()->findOrFail($superAdminId);

            $this->activityLog->record(
                action: 'registration_approved',
                module: 'auth',
                description: sprintf('Superadmin menyetujui registrasi pengguna %s.', $user->email),
                entity: $user,
                actor: $actor,
                properties: ['registration_request_id' => $request->id],
            );

            $this->notificationService->registrationApproved($user);

            return $request->fresh();
        });
    }
}
