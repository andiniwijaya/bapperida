<?php

namespace App\Services\Auth;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;

/**
 * Creates staff self-registration records pending Super Admin approval.
 *
 * Business rules:
 * - New users are staff role with pending status and verified email flow via Fortify.
 * - Creates a linked RegistrationRequest row for admin review.
 * - Runs inside a database transaction.
 *
 * Notification dispatch: registrationSubmitted after audit log.
 *
 * Related modules: User, Department, RegistrationRequest, Fortify (CreateNewUser).
 */
class RegisterService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * Persist a pending staff user and registration request.
     *
     * @param  array{name: string, username: string, email: string, password: string, department_id: int}  $data
     * @return User Newly created user with pending status.
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'department_id' => $data['department_id'],
            ]);

            $user->role = 'staff';
            $user->status = 'pending';
            $user->must_change_password = false;
            $user->save();

            event(new Registered($user));

            RegistrationRequest::create([

                'user_id' => $user->id,

                'status' => 'pending',

            ]);

            $this->activityLog->record(
                action: 'registration_submitted',
                module: 'auth',
                description: sprintf('Pengguna %s mengajukan registrasi akun (menunggu persetujuan).', $user->email),
                entity: $user,
                actor: $user,
            );

            $this->notificationService->registrationSubmitted($user);

            return $user;
        });
    }
}