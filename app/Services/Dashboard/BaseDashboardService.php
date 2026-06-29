<?php

namespace App\Services\Dashboard;

use App\Models\ActivityLog;
use App\Services\SystemSetting\SystemConfigurationService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shared helpers for dashboard presentation services.
 *
 * Business rules:
 * - Activity log queries are dashboard-specific audit views, not letter statistics.
 * - Letter aggregates must use ReportStatisticsService from child services.
 */
abstract class BaseDashboardService
{
    /**
     * Resolve cached system configuration for dashboard presentation limits.
     */
    protected function configuration(): SystemConfigurationService
    {
        return app(SystemConfigurationService::class);
    }

    /**
     * Recent activity log rows for dashboard tables.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function recentActivityLogs(?int $userId = null, ?int $limit = null): array
    {
        $limit ??= $this->configuration()->dashboardRecentActivityLimit();

        return ActivityLog::query()
            ->when($userId, fn ($query, $id) => $query->where('user_id', $id))
            ->latest('logged_at')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'action' => $log->action,
                'module' => $log->module,
                'description' => $log->description,
                'logged_at' => $log->logged_at?->toISOString(),
            ])
            ->all();
    }

    /**
     * Activity counts grouped by calendar period for system or user-scoped charts.
     *
     * @return array{labels: array<int, string>, counts: array<int, int>, granularity: string}
     */
    protected function activityTrend(
        ?int $userId = null,
        string $granularity = 'month',
        ?string $periodStart = null,
        ?string $periodEnd = null,
    ): array {
        $start = $periodStart
            ? Carbon::parse($periodStart)->startOfDay()
            : now()->subMonths(11)->startOfMonth();
        $end = $periodEnd
            ? Carbon::parse($periodEnd)->endOfDay()
            : now()->endOfMonth();

        $driver = DB::connection()->getDriverName();
        $expression = $this->activityPeriodExpression($granularity, $driver);

        $counts = ActivityLog::query()
            ->when($userId, fn ($query, $id) => $query->where('user_id', $id))
            ->whereBetween('logged_at', [$start, $end])
            ->selectRaw("{$expression} as period_key, COUNT(*) as aggregate_count")
            ->groupBy('period_key')
            ->pluck('aggregate_count', 'period_key');

        $labels = $this->buildActivityPeriodLabels($start, $end, $granularity);
        $keys = $this->buildActivityPeriodKeys($start, $end, $granularity);

        $series = collect($keys)
            ->map(fn (string $key) => (int) ($counts[$key] ?? 0))
            ->all();

        return [
            'labels' => $labels,
            'counts' => $series,
            'granularity' => $granularity,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function buildActivityPeriodLabels(CarbonInterface $start, CarbonInterface $end, string $granularity): array
    {
        $labels = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($granularity === 'day') {
                $labels[] = $cursor->format('d M Y');
                $cursor = $cursor->addDay();
            } elseif ($granularity === 'week') {
                $labels[] = 'Minggu '.$cursor->format('W Y');
                $cursor = $cursor->addWeek();
            } elseif ($granularity === 'year') {
                $labels[] = $cursor->format('Y');
                $cursor = $cursor->addYear();
            } else {
                $labels[] = $cursor->format('M Y');
                $cursor = $cursor->addMonth();
            }
        }

        return $labels;
    }

    /**
     * @return array<int, string>
     */
    private function buildActivityPeriodKeys(CarbonInterface $start, CarbonInterface $end, string $granularity): array
    {
        $keys = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($granularity === 'day') {
                $keys[] = $cursor->format('Y-m-d');
                $cursor = $cursor->addDay();
            } elseif ($granularity === 'week') {
                $keys[] = $cursor->format('Y-W');
                $cursor = $cursor->addWeek();
            } elseif ($granularity === 'year') {
                $keys[] = $cursor->format('Y');
                $cursor = $cursor->addYear();
            } else {
                $keys[] = $cursor->format('Y-m');
                $cursor = $cursor->addMonth();
            }
        }

        return $keys;
    }

    private function activityPeriodExpression(string $granularity, string $driver): string
    {
        if ($driver === 'sqlite') {
            return match ($granularity) {
                'day' => "strftime('%Y-%m-%d', logged_at)",
                'week' => "strftime('%Y-%W', logged_at)",
                'year' => "strftime('%Y', logged_at)",
                default => "strftime('%Y-%m', logged_at)",
            };
        }

        return match ($granularity) {
            'day' => "DATE_FORMAT(logged_at, '%Y-%m-%d')",
            'week' => "DATE_FORMAT(logged_at, '%Y-%u')",
            'year' => "DATE_FORMAT(logged_at, '%Y')",
            default => "DATE_FORMAT(logged_at, '%Y-%m')",
        };
    }

    /**
     * Build quick-action metadata for dashboard UI buttons.
     *
     * @param  array<int, array{label: string, href: string, variant?: string, class?: string}>  $actions
     * @return array<int, array<string, string>>
     */
    protected function quickActions(array $actions): array
    {
        return $actions;
    }
}
