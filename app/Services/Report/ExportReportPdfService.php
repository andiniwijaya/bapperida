<?php

namespace App\Services\Report;

use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use App\Services\SystemSetting\SystemConfigurationService;
use App\Support\ReportExportSchema;
use App\Support\ReportPaper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Generates PDF downloads from the report print Blade template (F4, official kop).
 */
class ExportReportPdfService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private SystemConfigurationService $configuration,
        private NotificationService $notificationService,
    ) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $columns
     */
    public function handle(
        Collection $rows,
        string $reportType,
        string $reportTypeLabel,
        array $filters,
        string $printedBy,
        array $columns,
    ): Response {
        $this->activityLog->record(
            action: 'export_pdf',
            module: 'report',
            description: sprintf('Pengguna mengekspor laporan %s ke PDF.', $reportTypeLabel),
            properties: [
                'report_type' => $reportType,
                'count' => $rows->count(),
            ],
        );

        $this->notificationService->reportExportPdf(Auth::user(), $reportTypeLabel);

        $fileName = sprintf('bapperida-laporan-%s.pdf', now()->format('YmdHis'));

        $pdf = Pdf::loadView('reports.print', [
            'rows' => $rows,
            'reportType' => $reportType,
            'reportTypeLabel' => $reportTypeLabel,
            'filters' => $filters,
            'printedAt' => now(),
            'printedBy' => $printedBy,
            'pdfMode' => true,
            'columns' => $columns,
            'exportColumns' => ReportExportSchema::columns($reportType),
            'reportBranding' => $this->configuration->reportBranding(),
        ])->setPaper(ReportPaper::f4Portrait());

        return $pdf->download($fileName);
    }
}
