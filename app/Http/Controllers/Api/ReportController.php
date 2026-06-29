<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\ReportRequest;
use App\Http\Requests\Api\ReportStatisticsRequest;
use App\Models\Report;
use App\Services\Report\ExportReportExcelService;
use App\Services\Report\ListReportService;
use App\Services\Report\ReportFilterService;
use App\Services\Report\ReportStatisticsService;
use Illuminate\Http\JsonResponse;

/**
 * API endpoints for report listing, filters, statistics, and Excel export.
 *
 * Business rules:
 * - Authorization via ReportPolicy (viewAny on Report marker).
 * - Statistics are served exclusively by ReportStatisticsService for Dashboard reuse.
 *
 * Related modules: ListReportService, ReportStatisticsService, ReportFilterService.
 */
class ReportController extends ApiController
{
    /** @var array<string, string> */
    private array $reportTypes = [
        'all' => 'Semua',
        'registration' => 'Registrasi Penomoran',
        'incoming' => 'Arsip Surat Masuk',
        'outgoing' => 'Arsip Surat Keluar',
    ];

    /**
     * Paginated report rows for the requested filters.
     */
    public function index(ReportRequest $request, ListReportService $service): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        $filters = $this->listFiltersFromRequest($request);
        $results = $service->handle($filters);

        return $this->success([
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ], 'Report data retrieved successfully.');
    }

    /**
     * Official application statistics (source of truth for Dashboard and analytics).
     */
    public function statistics(ReportStatisticsRequest $request, ReportStatisticsService $service): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        $filters = [
            'department_id' => $request->integer('department_id') ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'year' => $request->integer('year') ?: null,
            'month' => $request->integer('month') ?: null,
            'period_start' => $request->string('period_start')->trim()->toString() ?: null,
            'period_end' => $request->string('period_end')->trim()->toString() ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'letter_type' => $request->string('letter_type')->trim()->toString() ?: null,
            'granularity' => $request->string('granularity')->trim()->toString() ?: 'month',
        ];

        return $this->success(
            $service->handle($filters),
            'Report statistics retrieved successfully.'
        );
    }

    /**
     * Filter metadata for report UIs (years, departments, report types).
     */
    public function filters(ReportFilterService $service): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        return $this->success(
            $service->handle(),
            'Report filters retrieved successfully.'
        );
    }

    /**
     * Export filtered report rows to Excel.
     */
    public function exportExcel(ReportRequest $request, ListReportService $service, ExportReportExcelService $exportService)
    {
        $this->authorize('viewAny', Report::class);

        $filters = array_merge($this->listFiltersFromRequest($request), [
            'per_page' => null,
            'page' => 1,
        ]);

        $rows = $service->handle($filters);

        return $exportService->handle(
            $rows,
            $filters['report_type'],
            $this->reportTypes[$filters['report_type']] ?? $this->reportTypes['all'],
            $filters,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function listFiltersFromRequest(ReportRequest $request): array
    {
        return [
            'report_type' => $request->string('report_type')->trim()->toString() ?: 'all',
            'search' => $request->string('search')->trim()->toString() ?: null,
            'department_id' => $request->integer('department_id') ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'year' => $request->integer('year') ?: null,
            'month' => $request->integer('month') ?: null,
            'period_start' => $request->string('period_start')->trim()->toString() ?: null,
            'period_end' => $request->string('period_end')->trim()->toString() ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'letter_type' => $request->string('letter_type')->trim()->toString() ?: null,
            'per_page' => $request->integer('per_page', 10),
            'page' => $request->integer('page', 1),
            'order' => $request->input('order'),
        ];
    }
}
