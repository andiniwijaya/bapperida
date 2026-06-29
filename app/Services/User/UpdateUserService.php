<?php



namespace App\Services\User;



use App\Models\User;

use App\Services\ActivityLog\RecordActivityLogService;



/**

 * Updates core profile fields for an existing user (Super Admin action).

 *

 * Business rules:

 * - Email change clears email_verified_at.

 * - Username and email normalized to lowercase.

 * - Does not change role or status (separate services).

 *

 * Audit trail: records profile update on target user entity.

 *

 * Related modules: User, UserController.

 */

class UpdateUserService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Apply profile updates to the target user.

     *

     * @param  User  $user  Target user.

     * @param  array{name: string, username: string, email: string, department_id: int}  $data

     * @return User Refreshed user model.

     */

    public function handle(

        User $user,

        array $data

    ): User {

        UserManagementGuard::ensureAdminManagesStaffOnly($user);

        $emailChanged = strtolower($data['email']) !== $user->email;



        $user->update([

            'name' => $data['name'],

            'username' => strtolower($data['username']),

            'email' => strtolower($data['email']),

            'department_id' => $data['department_id'],

        ]);



        if ($emailChanged) {

            $user->email_verified_at = null;

            $user->save();

        }



        $user = $user->fresh();



        $this->activityLog->record(

            action: 'user_updated',

            module: 'user',

            description: sprintf('Data pengguna %s berhasil diperbarui.', $user->email),

            entity: $user,

        );



        return $user;

    }

}

