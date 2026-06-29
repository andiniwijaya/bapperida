<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Report\ReportStatisticsService;

/**
 * Admin dashboard presentation payload.
 *
 * Business rules:
 * - Shows organization-wide letter metrics (not superadmin user-management totals).
 * - Period widgets (today/week/month) use ReportStatisticsService letterSummary with date bounds.
 *
 * ReportStatisticsService: letterSummary(), monthlyTrends(), recentLetterItems().
 */
class AdminDashboardService extends BaseDashboardService
{
    public function __construct(private ReportStatisticsService $statisticsService) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(User $user, array $filters = []): array
    {
        $normalized = $this->statisticsService->normalizeFilters($filters);
        $granularity = $normalized['granularity'] ?? 'month';
        $summary = $this->statisticsService->letterSummary($normalized);
        $periodTrends = $this->statisticsService->countsByPeriod($normalized, $granularity);

        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $lettersToday = $this->statisticsService->letterSummary(array_merge($normalized, [
            'period_start' => $today,
            'period_end' => $today,
        ]));
        $lettersWeek = $this->statisticsService->letterSummary(array_merge($normalized, [
            'period_start' => $weekStart,
            'period_end' => $weekEnd,
        ]));
        $lettersMonth = $this->statisticsService->letterSummary(array_merge($normalized, [
            'period_start' => $monthStart,
            'period_end' => $monthEnd,
        ]));

        return [
            'role' => 'admin',
            'granularity' => $granularity,
            'widgets' => [
                'registration' => $summary['registration'],
                'incoming' => $summary['incoming'],
                'outgoing' => $summary['outgoing'],
                'letters_today' => $lettersToday['total'],
                'letters_this_week' => $lettersWeek['total'],
                'letters_this_month' => $lettersMonth['total'],
            ],
            'summary' => $summary,
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
                ['label' => 'Tambah Staff', 'href' => url('/users/create'), 'variant' => 'secondary'],
                ['label' => 'Tambah Surat Masuk', 'href' => route('incoming-letters.create'), 'variant' => 'secondary', 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
                ['label' => 'Tambah Surat Keluar', 'href' => route('outgoing-letters.create'), 'variant' => 'secondary', 'class' => 'bg-orange-500 hover:bg-orange-600'],
                ['label' => 'Registrasi Penomoran', 'href' => route('letter-number-registrations.create'), 'variant' => 'secondary'],
                ['label' => 'Export', 'href' => route('reports.index'), 'variant' => 'secondary'],
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
            'last_updated_at' => now()->toISOString(),
        ];
    }
}
