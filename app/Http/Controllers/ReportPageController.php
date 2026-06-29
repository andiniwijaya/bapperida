<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\Report\ExportReportPdfService;
use App\Services\Report\ListReportService;
use App\Services\Report\LogReportPrintService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Web routes for report index, print preview, and PDF export.
 *
 * Business rules:
 * - Authorization via ReportPolicy; filters validated inline for web print/export.
 * - Listing and export delegate to ListReportService (no aggregation in controller).
 *
 * Related modules: ListReportService, ExportReportPdfService.
 */
class ReportPageController extends Controller
{
    /** @var array<string, string> */
    private array $reportTypes = [
        'all' => 'Semua',
        'registration' => 'Registrasi Penomoran',
        'incoming' => 'Arsip Surat Masuk',
        'outgoing' => 'Arsip Surat Keluar',
    ];

    /**
     * Report module landing page.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Report::class);

        return view('reports.index');
    }

    /**
     * Printable HTML view of filtered report rows.
     */
    public function print(
        Request $request,
        ListReportService $service,
        LogReportPrintService $printLog,
        SystemConfigurationService $configuration,
    ): View {
        $this->authorize('viewAny', Report::class);

        $filters = $this->normalizeFilters($request);
        $printLog->record($filters);
        $rows = $service->handle($filters);
        $columns = $service->columnsForReportType($filters['report_type']);
        $exportColumns = $service->exportColumnsForReportType($filters['report_type']);

        return view('reports.print', [
            'rows' => $rows,
            'reportType' => $filters['report_type'],
            'reportTypeLabel' => $this->reportTypes[$filters['report_type']] ?? $this->reportTypes['all'],
            'filters' => $filters,
            'printedAt' => now(),
            'printedBy' => auth()->user()?->name ?? 'System',
            'pdfMode' => false,
            'columns' => $columns,
            'exportColumns' => $exportColumns,
            'reportBranding' => $configuration->reportBranding(),
        ]);
    }

    /**
     * PDF download of filtered report rows.
     */
    public function exportPdf(Request $request, ListReportService $service, ExportReportPdfService $exportService)
    {
        $this->authorize('viewAny', Report::class);

        $filters = $this->normalizeFilters($request);
        $rows = $service->handle($filters);
        $columns = $service->columnsForReportType($filters['report_type']);

        return $exportService->handle(
            $rows,
            $filters['report_type'],
            $this->reportTypes[$filters['report_type']] ?? $this->reportTypes['all'],
            $filters,
            auth()->user()?->name ?? 'System',
            $columns,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFilters(Request $request): array
    {
        $request->validate([
            'report_type' => ['nullable', 'string', Rule::in(['all', 'registration', 'incoming', 'outgoing'])],
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'letter_type' => ['nullable', 'string', Rule::in(array_keys(config('letter.types')))],
        ]);

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
            'per_page' => null,
            'page' => 1,
        ];
    }
}
