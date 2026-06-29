<?php

namespace App\Policies;

use App\Models\Dashboard;
use App\Models\User;

/**
 * Role-based authorization for dashboard pages and API.
 *
 * Business rules:
 * - superadmin, admin, and staff each access only their own dashboard variant.
 * - Cross-role dashboard access is denied to prevent data leakage.
 *
 * Related modules: Dashboard, DashboardController, DashboardPageController.
 */
class DashboardPolicy
{
    /**
     * Determine whether the user may access any dashboard.
     */
    public function view(User $user): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ], true);
    }

    /**
     * Super Admin dashboard is restricted to superadmin role.
     */
    public function viewSuperAdmin(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Admin dashboard is restricted to admin role.
     */
    public function viewAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Staff dashboard is restricted to staff role.
     */
    public function viewStaff(User $user): bool
    {
        return $user->role === 'staff';
    }
}
