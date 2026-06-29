<?php

namespace App\Services\ActivityLog;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Exports filtered activity logs to Excel for compliance reporting.
 *
 * Business rules:
 * - Export authorization enforced in ActivityLogController via ActivityLogPolicy::export.
 *
 * Audit trail: provides offline audit evidence for administrators.
 */
class ExportActivityLogExcelService
{
    /**
     * @param  LengthAwarePaginator|\Illuminate\Support\Collection  $logs
     */
    public function handle($logs): BinaryFileResponse
    {
        $rows = $logs instanceof LengthAwarePaginator
            ? $logs->getCollection()
            : collect($logs);

        $fileName = sprintf('bapperida-activity-logs-%s.xlsx', now()->format('YmdHis'));

        return ExcelFacade::download(new class($rows) implements FromCollection, WithHeadings {
            public function __construct(protected $rows) {}

            public function collection()
            {
                return $this->rows->map(fn ($log) => [
                    $log->logged_at?->format('Y-m-d H:i:s'),
                    $log->user?->name,
                    $log->user_role,
                    $log->department?->name,
                    $log->module,
                    $log->action,
                    $log->entity_type,
                    $log->entity_id,
                    $log->description,
                    $log->ip_address,
                    $log->method,
                    $log->url,
                ]);
            }

            public function headings(): array
            {
                return [
                    'Waktu',
                    'User',
                    'Role',
                    'Bidang',
                    'Modul',
                    'Aksi',
                    'Tipe Entitas',
                    'ID Entitas',
                    'Deskripsi',
                    'IP',
                    'Method',
                    'URL',
                ];
            }
        }, $fileName, Excel::XLSX);
    }
}
