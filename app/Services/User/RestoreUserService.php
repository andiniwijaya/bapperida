<?php



namespace App\Services\User;



use App\Models\User;

use App\Services\ActivityLog\RecordActivityLogService;



/**

 * Restores a soft-deleted user record.

 *

 * Audit trail: records user restoration with target user entity reference.

 *

 * Related modules: User, UserController, UserPolicy.

 */

class RestoreUserService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Restore the user and clear the deleted_by audit field.

     *

     * @param  User  $user  Trashed user model.

     * @return User Refreshed active user.

     */

    public function handle(User $user): User

    {

        UserManagementGuard::ensureAdminManagesStaffOnly($user);

        $user->restore();



        $user->deleted_by = null;

        $user->save();



        $user = $user->fresh();



        $this->activityLog->record(

            action: 'user_restored',

            module: 'user',

            description: sprintf('Pengguna %s berhasil dipulihkan.', $user->email),

            entity: $user,

        );



        return $user;

    }

}

