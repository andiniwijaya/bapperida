<?php

namespace App\Services\Report;

use App\Exports\ReportExcelExport;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use App\Services\SystemSetting\SystemConfigurationService;
use App\Support\ReportExportSchema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Generates Excel downloads for filtered report rows with official kop surat layout.
 */
class ExportReportExcelService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
        private SystemConfigurationService $configuration,
    ) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $filters
     */
    public function handle(
        Collection $rows,
        string $reportType,
        string $reportTypeLabel,
        array $filters,
    ): BinaryFileResponse {
        $this->activityLog->record(
            action: 'export_excel',
            module: 'report',
            description: sprintf('Pengguna mengekspor laporan %s ke Excel.', $reportTypeLabel),
            properties: [
                'report_type' => $reportType,
                'count' => $rows->count(),
            ],
        );

        $this->notificationService->reportExportExcel(Auth::user(), $reportTypeLabel);

        $fileName = sprintf('bapperida-laporan-%s.xlsx', now()->format('YmdHis'));

        return ExcelFacade::download(
            new ReportExcelExport(
                $rows,
                $reportTypeLabel,
                ReportExportSchema::columns($reportType),
                $filters,
                Auth::user()?->name ?? 'System',
                now(),
                $this->configuration->reportBranding(),
            ),
            $fileName,
            Excel::XLSX,
        );
    }
}
