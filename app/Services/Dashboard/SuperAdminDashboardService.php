<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Report\ReportStatisticsService;
use Illuminate\Support\Facades\Storage;

/**
 * Super Admin dashboard presentation payload.
 *
 * Business rules:
 * - Letter and org totals come from ReportStatisticsService only.
 * - Pending user approvals and activity logs are operational metrics, not letter statistics.
 *
 * ReportStatisticsService: totals(), monthlyTrends(), recentLetterItems(), topDepartments().
 */
class SuperAdminDashboardService extends BaseDashboardService
{
    public function __construct(private ReportStatisticsService $statisticsService) {}

    /**
     * @param  array<string, mixed>  $filters  Optional department and period filters.
     * @return array<string, mixed>
     */
    public function handle(User $user, array $filters = []): array
    {
        $normalized = $this->statisticsService->normalizeFilters($filters);
        $granularity = $normalized['granularity'] ?? 'month';
        $totals = $this->statisticsService->totals($normalized);
        $periodTrends = $this->statisticsService->countsByPeriod($normalized, $granularity);

        return [
            'role' => 'superadmin',
            'granularity' => $granularity,
            'widgets' => [
                'total_users' => $totals['users'],
                'total_departments' => $totals['departments'],
                'total_registration' => $totals['registration'],
                'total_incoming' => $totals['incoming'],
                'total_outgoing' => $totals['outgoing'],
                'pending_approval_users' => User::query()
                    ->where('status', 'pending')
                    ->count(),
            ],
            'summary' => $this->statisticsService->letterSummary($normalized),
            'summary_growth' => $this->statisticsService->summaryGrowth($normalized),
            'charts' => [
                'incoming_monthly' => [
                    'labels' => $periodTrends['labels'],
                    'data' => $periodTrends['incoming'],
                ],
                'outgoing_monthly' => [
                    'labels' => $periodTrends['labels'],
                    'data' => $periodTrends['outgoing'],
                ],
                'registration_monthly' => [
                    'labels' => $periodTrends['labels'],
                    'data' => $periodTrends['registration'],
                ],
                'system_activity' => $this->activityTrend(
                    null,
                    $granularity,
                    $normalized['period_start'],
                    $normalized['period_end'],
                ),
            ],
            'quick_actions' => $this->quickActions([
                ['label' => 'Persetujuan Registrasi', 'href' => url('/registration-requests'), 'variant' => 'secondary'],
                ['label' => 'Tambah Admin', 'href' => url('/users/create?role=admin'), 'variant' => 'secondary'],
                ['label' => 'Tambah Staff', 'href' => url('/users/create?role=staff'), 'variant' => 'secondary'],
                ['label' => 'Tambah Bidang', 'href' => url('/departments/create'), 'variant' => 'secondary'],
                ['label' => 'Registrasi Penomoran', 'href' => route('letter-number-registrations.create'), 'variant' => 'secondary'],
                ['label' => 'Export Report', 'href' => route('reports.index'), 'variant' => 'secondary', 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
            ]),
            'tables' => [
                'recent_items' => $this->statisticsService->recentLetterItems(
                    $normalized,
                    $this->configuration()->dashboardTableRowLimit()
                ),
                'activity_logs' => $this->recentActivityLogs(),
                'top_departments' => $this->statisticsService->topDepartments($normalized),
            ],
            'monthly_trends' => $periodTrends,
            'storage' => $this->storageSummary(),
            'last_updated_at' => now()->toISOString(),
        ];
    }

    /**
     * Optional storage footprint for uploaded letter files.
     *
     * @return array{used_mb: float|null, label: string}|null
     */
    private function storageSummary(): ?array
    {
        $paths = ['incoming-letters', 'outgoing-letters'];
        $bytes = 0;

        foreach ($paths as $path) {
            if (Storage::disk('local')->exists($path)) {
                foreach (Storage::disk('local')->allFiles($path) as $file) {
                    $bytes += Storage::disk('local')->size($file);
                }
            }
        }

        if ($bytes === 0) {
            return null;
        }

        return [
            'label' => 'Penyimpanan Arsip',
            'used_mb' => round($bytes / 1024 / 1024, 2),
        ];
    }
}
