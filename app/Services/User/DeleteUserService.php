<?php



namespace App\Services\User;



use App\Models\User;

use App\Services\ActivityLog\RecordActivityLogService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



/**

 * Soft-deletes a user and revokes all API tokens.

 *

 * Business rules:

 * - Cannot delete own account or any superadmin account.

 * - Sets deleted_by to the acting Super Admin for audit.

 *

 * Audit trail: records user deletion with target user entity reference.

 *

 * Related modules: User, UserController, UserPolicy.

 */

class DeleteUserService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Validate business rules, record deleter, revoke tokens, and soft-delete.

     *

     * @param  User  $user  Target user.

     *

     * @throws ValidationException When self-delete or superadmin deletion is attempted.

     */

    public function handle(User $user): void

    {

        UserManagementGuard::ensureAdminManagesStaffOnly($user);

        if ($user->id === Auth::id()) {

            throw ValidationException::withMessages([

                'user' => 'Anda tidak dapat menghapus akun sendiri.',

            ]);

        }



        if ($user->isSuperAdmin()) {

            throw ValidationException::withMessages([

                'user' => 'Akun Super Admin tidak dapat dihapus.',

            ]);

        }



        $user->deleted_by = Auth::id();

        $user->save();



        $user->tokens()->delete();

        $user->delete();



        $this->activityLog->record(

            action: 'user_deleted',

            module: 'user',

            description: sprintf('Pengguna %s berhasil dihapus.', $user->email),

            entity: $user,

        );

    }

}

