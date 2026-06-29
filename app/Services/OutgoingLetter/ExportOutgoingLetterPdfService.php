<?php



namespace App\Services\OutgoingLetter;



use App\Models\OutgoingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Response;

use Illuminate\Support\Collection;



/**

 * Generates PDF export for outgoing letter archives.

 *

 * Audit trail: records PDF export at start of handle with record count.

 *

 * Related modules: OutgoingLetter, DomPDF.

 */

class ExportOutgoingLetterPdfService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Render outgoing letters to PDF and trigger download.

     *

     * @param  Collection<int, OutgoingLetter>  $outgoingLetters  Records to export.

     * @return Response PDF file download.

     */

    public function handle(Collection $outgoingLetters): Response

    {

        $this->activityLog->record(

            action: 'export_pdf',

            module: 'outgoing_letter',

            description: sprintf('Pengguna mengekspor %d arsip surat keluar ke PDF.', $outgoingLetters->count()),

            properties: ['count' => $outgoingLetters->count()],

        );



        $fileName = sprintf('bapperida-outgoing-letters-%s.pdf', now()->format('YmdHis'));



        $pdf = Pdf::loadView('pdf.outgoing-letters.print', [

            'outgoingLetters' => $outgoingLetters,

            'pdfMode' => true,

        ])->setPaper('a4', 'landscape');



        return $pdf->download($fileName);

    }

}

