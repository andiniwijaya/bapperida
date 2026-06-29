<?php

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;

/**
 * Authorization for system configuration access.
 *
 * Business rules:
 * - Only Super Admin may view or modify system configuration.
 * - Admin and staff are denied to prevent configuration governance bypass.
 */
class SystemSettingPolicy
{
    /**
     * Listing is not exposed; mirrors view for singleton resource.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Only Super Admin may read current configuration.
     */
    public function view(User $user, SystemSetting $model): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Only Super Admin may change system configuration.
     */
    public function update(User $user, SystemSetting $model): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Settings are not created via API (seeded singleton).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Settings cannot be deleted via API.
     */
    public function delete(User $user, SystemSetting $model): bool
    {
        return false;
    }
}
