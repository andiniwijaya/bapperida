<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\GetUnreadNotificationCountService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Orchestrates role-specific dashboard presentation services.
 *
 * Business rules:
 * - Routes each role to a dedicated dashboard service (no shared widget hiding).
 * - All letter statistics delegate to ReportStatisticsService via child services.
 * - Dashboard view audit is optional when activity_log_audit_enabled is false.
 * - Includes notifications_unread_count for the notification badge.
 *
 * Configuration impact: respects activity_log_audit_enabled for dashboard_viewed logs.
 */
class DashboardService
{
    public function __construct(
        private SuperAdminDashboardService $superAdminDashboard,
        private AdminDashboardService $adminDashboard,
        private StaffDashboardService $staffDashboard,
        private RecordActivityLogService $activityLog,
        private SystemConfigurationService $configuration,
        private GetUnreadNotificationCountService $notificationCount,
    ) {}

    /**
     * Build dashboard payload for the authenticated user's role.
     *
     * @param  array{department_id?: int|null, period_start?: string|null, period_end?: string|null}  $filters
     * @return array<string, mixed>
     *
     * @throws AuthorizationException
     */
    public function handle(User $user, array $filters = []): array
    {
        $payload = match ($user->role) {
            'superadmin' => $this->superAdminDashboard->handle($user, $filters),
            'admin' => $this->adminDashboard->handle($user, $filters),
            'staff' => $this->staffDashboard->handle($user, $filters),
            default => throw new AuthorizationException('Dashboard is not available for this role.'),
        };

        if ($this->configuration->activityLogAuditEnabled()) {
            $this->activityLog->record(
                action: 'dashboard_viewed',
                module: 'dashboard',
                description: sprintf(
                    'Pengguna menampilkan dashboard %s dengan filter department_id=%s, period_start=%s, period_end=%s',
                    $user->role,
                    $filters['department_id'] ?? 'all',
                    $filters['period_start'] ?? 'all',
                    $filters['period_end'] ?? 'all'
                ),
                actor: $user,
                properties: [
                    'role' => $user->role,
                    'department_id' => $filters['department_id'] ?? null,
                    'period_start' => $filters['period_start'] ?? null,
                    'period_end' => $filters['period_end'] ?? null,
                ],
            );
        }

        $payload['notifications_unread_count'] = $this->notificationCount->handle($user);

        return $payload;
    }
}
