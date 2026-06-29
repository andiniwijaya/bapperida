<?php

namespace App\Services\IncomingLetter;

use App\Services\ActivityLog\RecordActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * Generates PDF export for incoming letter archives.
 *
 * Audit trail: records PDF export at start of handle with record count.
 */
class ExportIncomingLetterPdfService
{
    public function __construct(private RecordActivityLogService $activityLog) {}

    /**
     * Render incoming letters to PDF and trigger download.
     *
     * @param  Collection<int, \App\Models\IncomingLetter>  $incomingLetters  Records to export.
     * @return Response PDF file download.
     */
    public function handle(Collection $incomingLetters): Response
    {
        $this->activityLog->record(
            action: 'export_pdf',
            module: 'incoming_letter',
            description: sprintf('Pengguna mengekspor %d arsip surat masuk ke PDF.', $incomingLetters->count()),
            properties: ['count' => $incomingLetters->count()],
        );

        $fileName = sprintf('bapperida-incoming-letters-%s.pdf', now()->format('YmdHis'));

        $pdf = Pdf::loadView('pdf.incoming-letters.print', [
            'incomingLetters' => $incomingLetters,
            'pdfMode' => true,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}
