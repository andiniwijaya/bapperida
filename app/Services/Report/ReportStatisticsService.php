<?php

namespace App\Services\Report;

use App\Models\Department;
use App\Models\IncomingLetter;
use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Central statistics service for letter modules and organizational metrics.
 *
 * Business rules:
 * - All aggregate counts and trends for Dashboard and Report API must originate here.
 * - Filters: department, user, year, month, period range, status, letter type.
 * - Letter totals exclude soft-deleted rows; user/department totals use active records.
 *
 * Dashboard: DashboardService delegates summary, growth, trends, and top departments here.
 * Related modules: LetterNumberRegistration, IncomingLetter, OutgoingLetter, User, Department.
 */
class ReportStatisticsService
{
    /**
     * Build full statistics payload for the report statistics API.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $granularity = $filters['granularity'] ?? 'month';

        return [
            'totals' => $this->totals($filters),
            'by_period' => $this->countsByPeriod($filters, $granularity),
            'by_department' => $this->countsByDepartment($filters),
            'by_user' => $this->countsByUser($filters),
            'by_status' => $this->countsByStatus($filters),
            'by_letter_type' => $this->countsByLetterType($filters),
            'last_updated_at' => now()->toISOString(),
        ];
    }

    /**
     * Letter module summary counts for dashboard summary cards.
     *
     * @param  array<string, mixed>  $filters
     * @return array{registration: int, incoming: int, outgoing: int, total: int}
     */
    public function letterSummary(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $registrationCount = $this->countRegistrations($filters);
        $incomingCount = $this->countIncomingLetters($filters);
        $outgoingCount = $this->countOutgoingLetters($filters);

        return [
            'registration' => $registrationCount,
            'incoming' => $incomingCount,
            'outgoing' => $outgoingCount,
            'total' => $registrationCount + $incomingCount + $outgoingCount,
        ];
    }

    /**
     * Extended totals including users and departments for report statistics API.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    public function totals(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $letterSummary = $this->letterSummary($filters);

        return [
            'registration' => $letterSummary['registration'],
            'incoming' => $letterSummary['incoming'],
            'outgoing' => $letterSummary['outgoing'],
            'letters_total' => $letterSummary['total'],
            'users' => $this->countUsers($filters),
            'departments' => $this->countDepartments($filters),
        ];
    }

    /**
     * Percentage growth vs the immediately preceding period of equal length.
     *
     * Dashboard: summary_growth payload.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, float>
     */
    public function summaryGrowth(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $range = $this->resolvePeriodRange($filters);
        $previousRange = $this->resolvePreviousPeriodRange($range['start'], $range['end']);

        $currentFilters = array_merge($filters, [
            'period_start' => $range['start']->toDateString(),
            'period_end' => $range['end']->toDateString(),
        ]);

        $previousFilters = array_merge($filters, [
            'period_start' => $previousRange['start']->toDateString(),
            'period_end' => $previousRange['end']->toDateString(),
        ]);

        $current = $this->letterSummary($currentFilters);
        $previous = $this->letterSummary($previousFilters);

        return [
            'registration' => $this->growthRate($current['registration'], $previous['registration']),
            'incoming' => $this->growthRate($current['incoming'], $previous['incoming']),
            'outgoing' => $this->growthRate($current['outgoing'], $previous['outgoing']),
            'total' => $this->growthRate($current['total'], $previous['total']),
        ];
    }

    /**
     * Last 12 months of counts per letter module for chart rendering.
     *
     * Dashboard: monthly_trends payload.
     *
     * @param  array<string, mixed>  $filters
     * @return array{labels: array<int, string>, registration: array<int, int>, incoming: array<int, int>, outgoing: array<int, int>}
     */
    public function monthlyTrends(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();
        $labels = collect(range(0, 11))
            ->map(fn (int $offset) => $start->copy()->addMonths($offset)->format('M Y'))
            ->all();

        return [
            'labels' => $labels,
            'registration' => $this->monthlyCounts($this->registrationCountQuery($filters), 'letter_date', $start, $end),
            'incoming' => $this->monthlyCounts($this->incomingCountQuery($filters), 'received_date', $start, $end),
            'outgoing' => $this->monthlyOutgoingCounts($filters, $start, $end),
        ];
    }

    /**
     * Top departments by combined letter volume across all modules.
     *
     * Dashboard: top_departments payload.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array{name: string, count: int}>
     */
    public function topDepartments(array $filters = [], int $limit = 5): array
    {
        $filters = $this->normalizeFilters($filters);
        $departmentCounts = collect();

        $this->registrationCountQuery($filters)
            ->with('department')
            ->select(['id', 'department_id'])
            ->chunk(500, function ($registrations) use ($departmentCounts) {
                foreach ($registrations as $registration) {
                    $name = $registration->department?->name ?? 'Tanpa Bidang';
                    $departmentCounts->put($name, ($departmentCounts->get($name, 0) + 1));
                }
            });

        $this->incomingCountQuery($filters)
            ->with('department')
            ->select(['id', 'department_id'])
            ->chunk(500, function ($letters) use ($departmentCounts) {
                foreach ($letters as $letter) {
                    $name = $letter->department?->name ?? 'Tanpa Bidang';
                    $departmentCounts->put($name, ($departmentCounts->get($name, 0) + 1));
                }
            });

        $this->outgoingCountQuery($filters)
            ->with('registration.department')
            ->select(['id', 'letter_number_registration_id'])
            ->chunk(500, function ($letters) use ($departmentCounts) {
                foreach ($letters as $letter) {
                    $name = $letter->registration?->department?->name ?? 'Tanpa Bidang';
                    $departmentCounts->put($name, ($departmentCounts->get($name, 0) + 1));
                }
            });

        return $departmentCounts
            ->map(fn (int $count, string $name) => ['name' => $name, 'count' => $count])
            ->sortByDesc('count')
            ->values()
            ->take($limit)
            ->all();
    }

    /**
     * Counts grouped by calendar period within the resolved date range.
     *
     * @param  array<string, mixed>  $filters
     * @return array{labels: array<int, string>, registration: array<int, int>, incoming: array<int, int>, outgoing: array<int, int>}
     */
    public function countsByPeriod(array $filters, string $granularity = 'month'): array
    {
        $filters = $this->normalizeFilters($filters);
        $range = $this->resolvePeriodRange($filters);
        $periodFilters = array_merge($filters, [
            'period_start' => $range['start']->toDateString(),
            'period_end' => $range['end']->toDateString(),
        ]);

        $labels = $this->buildPeriodLabels($range['start'], $range['end'], $granularity);

        return [
            'granularity' => $granularity,
            'labels' => $labels,
            'registration' => $this->periodCounts(
                $this->registrationCountQuery($periodFilters),
                'letter_date',
                $range['start'],
                $range['end'],
                $granularity,
                $labels
            ),
            'incoming' => $this->periodCounts(
                $this->incomingCountQuery($periodFilters),
                'received_date',
                $range['start'],
                $range['end'],
                $granularity,
                $labels
            ),
            'outgoing' => $this->periodOutgoingCounts($periodFilters, $range['start'], $range['end'], $granularity, $labels),
        ];
    }

    /**
     * Letter counts per department name.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array{department_id: int|null, name: string, registration: int, incoming: int, outgoing: int, total: int}>
     */
    public function countsByDepartment(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $departments = Department::query()
            ->when($filters['department_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return $departments->map(function (Department $department) use ($filters) {
            $scoped = array_merge($filters, ['department_id' => $department->id]);
            $registration = $this->countRegistrations($scoped);
            $incoming = $this->countIncomingLetters($scoped);
            $outgoing = $this->countOutgoingLetters($scoped);

            return [
                'department_id' => $department->id,
                'name' => $department->name,
                'registration' => $registration,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'total' => $registration + $incoming + $outgoing,
            ];
        })->all();
    }

    /**
     * Letter counts per creator user.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array{user_id: int, name: string, registration: int, incoming: int, outgoing: int, total: int}>
     */
    public function countsByUser(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $userIds = collect()
            ->merge($this->registrationCountQuery($filters)->distinct()->pluck('created_by'))
            ->merge($this->incomingCountQuery($filters)->distinct()->pluck('created_by'))
            ->merge($this->outgoingCountQuery($filters)->distinct()->pluck('created_by'))
            ->filter()
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return $users->map(function (User $user) use ($filters) {
            $scoped = array_merge($filters, ['user_id' => $user->id]);
            $registration = $this->countRegistrations($scoped);
            $incoming = $this->countIncomingLetters($scoped);
            $outgoing = $this->countOutgoingLetters($scoped);

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'registration' => $registration,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'total' => $registration + $incoming + $outgoing,
            ];
        })->all();
    }

    /**
     * Letter counts grouped by status per module.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, array<string, int>>
     */
    public function countsByStatus(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        return [
            'registration' => $this->groupCountByColumn($this->registrationCountQuery($filters), 'status'),
            'incoming' => $this->groupCountByColumn($this->incomingCountQuery($filters), 'status'),
            'outgoing' => $this->groupCountByColumn($this->outgoingCountQuery($filters), 'status'),
        ];
    }

    /**
     * Letter counts grouped by classification / letter type per module.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, array<string, int>>
     */
    public function countsByLetterType(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        return [
            'registration' => $this->groupCountByColumn($this->registrationCountQuery($filters), 'letter_type'),
            'incoming' => $this->groupCountByColumn($this->incomingCountQuery($filters), 'letter_attribute'),
            'outgoing' => $this->groupCountByColumn($this->outgoingCountQuery($filters), 'letter_type'),
        ];
    }

    /**
     * Ten most recent letter events merged from all modules, sorted by date.
     *
     * Dashboard: recent_items payload (not aggregate statistics).
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function recentLetterItems(array $filters = [], int $limit = 10): array
    {
        $filters = $this->normalizeFilters($filters);

        $latestRegistrations = $this->registrationCountQuery($filters)
            ->with('department')
            ->latest('letter_date')
            ->limit($limit)
            ->get()
            ->map(fn (LetterNumberRegistration $registration) => [
                'id' => $registration->id,
                'type' => 'registration',
                'type_label' => 'Registrasi Penomoran',
                'letter_number' => $registration->letter_number,
                'department' => $registration->department?->name,
                'subject' => $registration->subject,
                'date' => $registration->letter_date?->format('Y-m-d'),
                'status' => $registration->status,
            ]);

        $latestIncoming = $this->incomingCountQuery($filters)
            ->with('department')
            ->latest('received_date')
            ->limit($limit)
            ->get()
            ->map(fn (IncomingLetter $letter) => [
                'id' => $letter->id,
                'type' => 'incoming',
                'type_label' => 'Surat Masuk',
                'letter_number' => $letter->letter_number,
                'department' => $letter->department?->name,
                'subject' => $letter->subject,
                'date' => $letter->received_date?->format('Y-m-d'),
                'status' => $letter->status,
            ]);

        $latestOutgoing = $this->outgoingCountQuery($filters)
            ->with('registration.department')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (OutgoingLetter $letter) => [
                'id' => $letter->id,
                'type' => 'outgoing',
                'type_label' => 'Surat Keluar',
                'letter_number' => $letter->registration?->letter_number,
                'department' => $letter->registration?->department?->name,
                'subject' => $letter->registration?->subject,
                'date' => $letter->registration?->letter_date?->format('Y-m-d'),
                'status' => $letter->status,
            ]);

        return $latestRegistrations
            ->concat($latestIncoming)
            ->concat($latestOutgoing)
            ->sortByDesc(fn (array $item) => $item['date'] ?? '')
            ->values()
            ->take($limit)
            ->all();
    }

    /**
     * Merge filter defaults and strip empty values.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function normalizeFilters(array $filters): array
    {
        return array_merge([
            'department_id' => null,
            'user_id' => null,
            'year' => null,
            'month' => null,
            'period_start' => null,
            'period_end' => null,
            'status' => null,
            'letter_type' => null,
            'granularity' => 'month',
        ], $filters);
    }

  /**
     * @param  array<string, mixed>  $filters
     */
    private function countRegistrations(array $filters): int
    {
        return $this->registrationCountQuery($filters)->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function countIncomingLetters(array $filters): int
    {
        return $this->incomingCountQuery($filters)->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function countOutgoingLetters(array $filters): int
    {
        return $this->outgoingCountQuery($filters)->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function countUsers(array $filters): int
    {
        return User::query()
            ->whereNull('deleted_at')
            ->when($filters['department_id'] ?? null, fn ($query, $id) => $query->where('department_id', $id))
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function countDepartments(array $filters): int
    {
        return Department::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->when($filters['department_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function registrationCountQuery(array $filters): Builder
    {
        return LetterNumberRegistration::query()
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->whereMonth('letter_date', $month))
            ->when($filters['period_start'] ?? null, fn ($query, $value) => $query->whereDate('letter_date', '>=', $value))
            ->when($filters['period_end'] ?? null, fn ($query, $value) => $query->whereDate('letter_date', '<=', $value))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'] ?? null, fn ($query, $type) => $query->where('letter_type', $type));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function incomingCountQuery(array $filters): Builder
    {
        return IncomingLetter::query()
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->whereYear('received_date', $year))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->whereMonth('received_date', $month))
            ->when($filters['period_start'] ?? null, fn ($query, $value) => $query->whereDate('received_date', '>=', $value))
            ->when($filters['period_end'] ?? null, fn ($query, $value) => $query->whereDate('received_date', '<=', $value))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'] ?? null, fn ($query, $type) => $query->where('letter_attribute', $type));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function outgoingCountQuery(array $filters): Builder
    {
        return OutgoingLetter::query()
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->whereHas('registration', fn ($q) => $q->where('department_id', $departmentId)))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('outgoing_letters.created_by', $userId))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->whereHas('registration', fn ($q) => $q->where('year', $year)))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->whereHas('registration', fn ($q) => $q->whereMonth('letter_date', $month)))
            ->when($filters['period_start'] ?? null, fn ($query, $value) => $query->whereHas('registration', fn ($q) => $q->whereDate('letter_date', '>=', $value)))
            ->when($filters['period_end'] ?? null, fn ($query, $value) => $query->whereHas('registration', fn ($q) => $q->whereDate('letter_date', '<=', $value)))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'] ?? null, fn ($query, $type) => $query->where('letter_type', $type));
    }

    /**
     * @return array{start: CarbonInterface, end: CarbonInterface}
     */
    private function resolvePeriodRange(array $filters): array
    {
        $start = isset($filters['period_start']) && $filters['period_start']
            ? Carbon::parse($filters['period_start'])->startOfDay()
            : now()->subMonth()->startOfMonth();

        $end = isset($filters['period_end']) && $filters['period_end']
            ? Carbon::parse($filters['period_end'])->endOfDay()
            : now()->endOfMonth();

        if (isset($filters['year']) && $filters['year'] && ! isset($filters['period_start'])) {
            $start = Carbon::create($filters['year'], 1, 1)->startOfDay();
            $end = Carbon::create($filters['year'], 12, 31)->endOfDay();
        }

        if (isset($filters['month']) && $filters['month'] && isset($filters['year']) && $filters['year']) {
            $start = Carbon::create($filters['year'], $filters['month'], 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * @return array{start: CarbonInterface, end: CarbonInterface}
     */
    private function resolvePreviousPeriodRange(CarbonInterface $start, CarbonInterface $end): array
    {
        $length = $start->diffInDays($end) + 1;

        return [
            'start' => $start->copy()->subDays($length)->startOfDay(),
            'end' => $start->copy()->subDay()->endOfDay(),
        ];
    }

    private function growthRate(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : 100.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

  /**
     * @param  Builder  $query
     */
    private function monthlyCounts($query, string $dateColumn, CarbonInterface $start, CarbonInterface $end): array
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', {$dateColumn})"
            : "DATE_FORMAT({$dateColumn}, '%Y-%m')";

        $counts = $query
            ->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$monthExpression} as period_key, COUNT(*) as aggregate_count")
            ->groupBy('period_key')
            ->pluck('aggregate_count', 'period_key');

        return collect(range(0, 11))
            ->map(fn (int $offset) => $start->copy()->addMonths($offset)->format('Y-m'))
            ->map(fn (string $month) => (int) ($counts[$month] ?? 0))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function monthlyOutgoingCounts(array $filters, CarbonInterface $start, CarbonInterface $end): array
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', letter_number_registrations.letter_date)"
            : "DATE_FORMAT(letter_number_registrations.letter_date, '%Y-%m')";

        $counts = $this->outgoingCountQuery($filters)
            ->join('letter_number_registrations', 'outgoing_letters.letter_number_registration_id', '=', 'letter_number_registrations.id')
            ->whereBetween('letter_number_registrations.letter_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$monthExpression} as period_key, COUNT(*) as aggregate_count")
            ->groupBy('period_key')
            ->pluck('aggregate_count', 'period_key');

        return collect(range(0, 11))
            ->map(fn (int $offset) => $start->copy()->addMonths($offset)->format('Y-m'))
            ->map(fn (string $month) => (int) ($counts[$month] ?? 0))
            ->all();
    }

    /**
     * @param  Builder  $query
     */
    private function periodCounts($query, string $dateColumn, CarbonInterface $start, CarbonInterface $end, string $granularity, array $labels): array
    {
        $driver = DB::connection()->getDriverName();
        $expression = $this->periodExpression($dateColumn, $granularity, $driver);

        $counts = $query
            ->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$expression} as period_key, COUNT(*) as aggregate_count")
            ->groupBy('period_key')
            ->pluck('aggregate_count', 'period_key');

        return collect($labels)
            ->map(fn (string $label) => (int) ($counts[$label] ?? 0))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function periodOutgoingCounts(array $filters, CarbonInterface $start, CarbonInterface $end, string $granularity, array $labels): array
    {
        $driver = DB::connection()->getDriverName();
        $expression = $this->periodExpression('letter_number_registrations.letter_date', $granularity, $driver);

        $counts = $this->outgoingCountQuery($filters)
            ->join('letter_number_registrations', 'outgoing_letters.letter_number_registration_id', '=', 'letter_number_registrations.id')
            ->whereBetween('letter_number_registrations.letter_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$expression} as period_key, COUNT(*) as aggregate_count")
            ->groupBy('period_key')
            ->pluck('aggregate_count', 'period_key');

        return collect($labels)
            ->map(fn (string $label) => (int) ($counts[$label] ?? 0))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function buildPeriodLabels(CarbonInterface $start, CarbonInterface $end, string $granularity): array
    {
        $labels = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($granularity === 'day') {
                $labels[] = $cursor->format('Y-m-d');
                $cursor = $cursor->addDay();
            } elseif ($granularity === 'week') {
                $labels[] = $cursor->format('Y-W');
                $cursor = $cursor->addWeek();
            } elseif ($granularity === 'year') {
                $labels[] = $cursor->format('Y');
                $cursor = $cursor->addYear();
            } else {
                $labels[] = $cursor->format('Y-m');
                $cursor = $cursor->addMonth();
            }
        }

        return $labels;
    }

    private function periodExpression(string $dateColumn, string $granularity, string $driver): string
    {
        if ($driver === 'sqlite') {
            return match ($granularity) {
                'day' => "strftime('%Y-%m-%d', {$dateColumn})",
                'week' => "strftime('%Y-%W', {$dateColumn})",
                'year' => "strftime('%Y', {$dateColumn})",
                default => "strftime('%Y-%m', {$dateColumn})",
            };
        }

        return match ($granularity) {
            'day' => "DATE_FORMAT({$dateColumn}, '%Y-%m-%d')",
            'week' => "DATE_FORMAT({$dateColumn}, '%Y-%u')",
            'year' => "DATE_FORMAT({$dateColumn}, '%Y')",
            default => "DATE_FORMAT({$dateColumn}, '%Y-%m')",
        };
    }

    /**
     * @param  Builder  $query
     * @return array<string, int>
     */
    private function groupCountByColumn($query, string $column): array
    {
        return $query
            ->selectRaw("{$column}, COUNT(*) as aggregate_count")
            ->groupBy($column)
            ->pluck('aggregate_count', $column)
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
