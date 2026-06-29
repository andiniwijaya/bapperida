<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

/**
 * Authorization for audit trail viewing and export.
 *
 * Business rules:
 * - Only superadmin and admin may view activity logs.
 * - Logs are immutable: create/update/delete denied for all roles via API.
 *
 * Audit trail: prevents tampering with compliance records.
 */
class ActivityLogPolicy
{
    /**
     * Determine whether the user can list activity logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'superadmin' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can view a single activity log entry.
     */
    public function view(User $user, ActivityLog $model): bool
    {
        return $user->role === 'superadmin' || $user->role === 'admin';
    }

    /**
     * Audit logs cannot be created via API (service layer only).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Audit logs are immutable.
     */
    public function update(User $user, ActivityLog $model): bool
    {
        return false;
    }

    /**
     * Audit logs cannot be deleted by staff or admin.
     */
    public function delete(User $user, ActivityLog $model): bool
    {
        return false;
    }

    /**
     * Permanent deletion is disabled for all roles.
     */
    public function forceDelete(User $user, ActivityLog $model): bool
    {
        return false;
    }

    /**
     * Export permission mirrors viewAny for admin and superadmin.
     */
    public function export(User $user): bool
    {
        return $this->viewAny($user);
    }
}
