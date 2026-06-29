<?php

namespace App\Policies;

use App\Models\User;

/**
 * Authorization policy for user management across Super Admin and Admin roles.
 *
 * Business rules:
 * - Super Admin has full user lifecycle control except force delete.
 * - Admin may manage staff accounts only (view, create, update, delete, restore, reset password).
 * - Role and status changes remain Super Admin only.
 */
class UserPolicy
{
    /**
     * Determine whether the user can view the user listing.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdmin($user) && $this->isStaff($model);
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdmin($user) && $this->isStaff($model);
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdmin($user) && $this->isStaff($model);
    }

    /**
     * Determine whether the user can restore a user.
     */
    public function restore(User $user, User $model): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdmin($user) && $this->isStaff($model);
    }

    /**
     * Determine whether the user can change another user's role.
     */
    public function changeRole(User $user, User $model): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can change another user's status.
     */
    public function changeStatus(User $user, User $model): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can resend password-setup email.
     */
    public function resendPasswordSetup(User $user, User $model): bool
    {
        if (! $model->must_change_password) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdmin($user) && $this->isStaff($model);
    }

    /**
     * Permanent deletion is disabled for all roles.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    private function isStaff(User $model): bool
    {
        return $model->role === 'staff';
    }
}
