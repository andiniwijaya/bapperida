<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Self-service account deletion (distinct from Super Admin DeleteUserService).
 *
 * Business rules:
 * - Super Admin accounts cannot delete themselves.
 * - Sets deleted_by to the user's own ID and revokes all tokens.
 *
 * Related modules: User, Auth.
 */
class DeleteAccountService
{
    /**
     * Soft-delete the authenticated user's own account.
     *
     * @param  User  $user  Account owner requesting deletion.
     *
     * @throws ValidationException When the user is a superadmin.
     */
    public function handle(User $user): void
    {
        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'user' => 'Akun Super Admin tidak dapat dihapus.',
            ]);
        }

        $user->deleted_by = $user->id;
        $user->save();

        $user->tokens()->delete();
        $user->delete();
    }
}
