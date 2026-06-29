<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\DashboardRequest;
use App\Models\Dashboard;
use App\Services\Dashboard\DashboardService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Http\JsonResponse;

/**
 * API endpoint for role-specific dashboard presentation data.
 *
 * Business rules:
 * - Authorization via DashboardPolicy per role.
 * - Default period filters come from SystemConfigurationService when omitted.
 *
 * Configuration impact: applies dashboard_default_period_days when no period filter sent.
 */
class DashboardController extends ApiController
{
    /**
     * Return role-specific dashboard payload and write an activity log entry.
     *
     * @param  DashboardRequest  $request  Optional department and period filters (admin/superadmin).
     * @param  DashboardService  $service  Role orchestrator.
     */
    public function index(
        DashboardRequest $request,
        DashboardService $service,
        SystemConfigurationService $configuration,
    ): JsonResponse {
        $user = $request->user();

        $this->authorize(match ($user->role) {
            'superadmin' => 'viewSuperAdmin',
            'admin' => 'viewAdmin',
            'staff' => 'viewStaff',
            default => 'view',
        }, Dashboard::class);

        $periodStart = $request->string('period_start')->trim()->toString() ?: null;
        $periodEnd = $request->string('period_end')->trim()->toString() ?: null;

        if ($periodStart === null && $periodEnd === null) {
            $defaults = $configuration->dashboardDefaultPeriod();
            $periodStart = $defaults['period_start'];
            $periodEnd = $defaults['period_end'];
        }

        $filters = [
            'department_id' => $request->integer('department_id') ?: null,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'granularity' => $request->string('granularity')->trim()->toString() ?: 'month',
        ];

        if ($user->role === 'staff') {
            $filters['department_id'] = null;
        }

        return $this->success(
            $service->handle($user, $filters),
            'Dashboard data retrieved successfully.'
        );
    }
}
