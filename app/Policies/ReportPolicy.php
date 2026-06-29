<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

/**
 * Role-based authorization for report listing, statistics, and exports.
 *
 * Business rules:
 * - superadmin, admin, and staff may view reports and statistics.
 * - Report module does not expose per-record mutations; viewAny covers all report actions.
 *
 * Related modules: Report, ReportController, ReportPageController, DashboardService.
 */
class ReportPolicy
{
    /**
     * Determine whether the user can view reports and statistics.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ], true);
    }

    /**
     * Report module has no single record resource; mirrors viewAny.
     */
    public function view(User $user, Report $report): bool
    {
        return $this->viewAny($user);
    }
}
