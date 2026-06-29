<?php

namespace App\Services\IncomingLetter;

use App\Exports\IncomingLetterExcelExport;
use App\Models\IncomingLetter;
use App\Services\ActivityLog\RecordActivityLogService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

/**
 * Generates Excel export for incoming letter archives.
 *
 * Audit trail: records Excel export at start of handle with record count.
 */
class ExportIncomingLetterExcelService
{
    public function __construct(private RecordActivityLogService $activityLog) {}

    /**
     * Build spreadsheet from incoming letter collection and trigger download.
     *
     * @param  Collection<int, IncomingLetter>  $incomingLetters  Records to export.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse XLSX download.
     */
    public function handle(Collection $incomingLetters): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->activityLog->record(
            action: 'export_excel',
            module: 'incoming_letter',
            description: sprintf('Pengguna mengekspor %d arsip surat masuk ke Excel.', $incomingLetters->count()),
            properties: ['count' => $incomingLetters->count()],
        );

        $fileName = sprintf('bapperida-incoming-letters-%s.xlsx', now()->format('YmdHis'));

        return ExcelFacade::download(
            IncomingLetterExcelExport::make($incomingLetters, auth()->user()?->name ?? 'System'),
            $fileName,
            Excel::XLSX,
        );
    }
}
