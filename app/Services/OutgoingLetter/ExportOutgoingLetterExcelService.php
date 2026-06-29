<?php

namespace App\Services\OutgoingLetter;

use App\Exports\OutgoingLetterExcelExport;
use App\Models\OutgoingLetter;
use App\Services\ActivityLog\RecordActivityLogService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

/**
 * Generates Excel export for outgoing letter archives.
 *
 * Audit trail: records Excel export at start of handle with record count.
 */
class ExportOutgoingLetterExcelService
{
    public function __construct(private RecordActivityLogService $activityLog) {}

    /**
     * Build spreadsheet from outgoing letter collection and trigger download.
     *
     * @param  Collection<int, OutgoingLetter>  $outgoingLetters  Records to export.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse XLSX download.
     */
    public function handle(Collection $outgoingLetters): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->activityLog->record(
            action: 'export_excel',
            module: 'outgoing_letter',
            description: sprintf('Pengguna mengekspor %d arsip surat keluar ke Excel.', $outgoingLetters->count()),
            properties: ['count' => $outgoingLetters->count()],
        );

        $fileName = sprintf('bapperida-outgoing-letters-%s.xlsx', now()->format('YmdHis'));

        return ExcelFacade::download(
            OutgoingLetterExcelExport::make($outgoingLetters, auth()->user()?->name ?? 'System'),
            $fileName,
            Excel::XLSX,
        );
    }
}
