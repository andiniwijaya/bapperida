<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

/**
 * Authorization policy for department master data management.
 *
 * Business rules:
 * - All abilities limited to superadmin role.
 * - forceDelete disabled to preserve soft-delete audit trail.
 *
 * Related modules: Department, DepartmentController.
 */
class DepartmentPolicy
{
    /**
     * Determine whether the user can view the department listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'superadmin' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can view a department.
     */
    public function view(User $user, Department $model): bool
    {
        return $user->role === 'superadmin' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create departments.
     */
    public function create(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can update a department.
     */
    public function update(User $user, Department $model): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can soft-delete a department.
     */
    public function delete(User $user, Department $model): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can restore a soft-deleted department.
     */
    public function restore(User $user, Department $model): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Permanent deletion is disabled for all roles.
     */
    public function forceDelete(User $user, Department $model): bool
    {
        return false;
    }
}
