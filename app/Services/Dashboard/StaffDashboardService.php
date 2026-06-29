<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Report\ReportStatisticsService;

/**
 * Staff dashboard presentation payload.
 *
 * Business rules:
 * - All letter statistics scoped to authenticated user via user_id filter.
 * - Staff cannot see organization-wide or other users' metrics.
 *
 * ReportStatisticsService: letterSummary(), monthlyTrends(), recentLetterItems() with user_id.
 */
class StaffDashboardService extends BaseDashboardService
{
    public function __construct(private ReportStatisticsService $statisticsService) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(User $user, array $filters = []): array
    {
        $scoped = $this->statisticsService->normalizeFilters(array_merge($filters, [
            'user_id' => $user->id,
            'department_id' => null,
        ]));
        $granularity = $scoped['granularity'] ?? 'month';

        $summary = $this->statisticsService->letterSummary($scoped);
        $periodTrends = $this->statisticsService->countsByPeriod($scoped, $granularity);

        return [
            'role' => 'staff',
            'granularity' => $granularity,
            'widgets' => [
                'my_registration' => $summary['registration'],
                'my_incoming' => $summary['incoming'],
                'my_outgoing' => $summary['outgoing'],
            ],
            'summary' => $summary,
            'charts' => [
                'my_activity' => $this->activityTrend(
                    $user->id,
                    $granularity,
                    $scoped['period_start'],
                    $scoped['period_end'],
                ),
                'my_letters_monthly' => [
                    'labels' => $periodTrends['labels'],
                    'registration' => $periodTrends['registration'],
                    'incoming' => $periodTrends['incoming'],
                    'outgoing' => $periodTrends['outgoing'],
                ],
            ],
            'quick_actions' => $this->quickActions([
                ['label' => 'Tambah Surat Masuk', 'href' => route('incoming-letters.create'), 'variant' => 'secondary', 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
                ['label' => 'Tambah Surat Keluar', 'href' => route('outgoing-letters.create'), 'variant' => 'secondary', 'class' => 'bg-orange-500 hover:bg-orange-600'],
                ['label' => 'Registrasi Penomoran', 'href' => route('letter-number-registrations.create'), 'variant' => 'secondary'],
            ]),
            'tables' => [
                'recent_items' => $this->statisticsService->recentLetterItems(
                    $scoped,
                    $this->configuration()->dashboardTableRowLimit()
                ),
                'activity_logs' => $this->recentActivityLogs($user->id),
            ],
            'monthly_trends' => $periodTrends,
            'last_updated_at' => now()->toISOString(),
        ];
    }
}
