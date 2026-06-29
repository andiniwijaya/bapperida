<?php

namespace App\Services\LetterNumberRegistration;

use App\Services\ActivityLog\RecordActivityLogService;
use App\Support\RegistrationCardPrint;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * Generates PDF export for Kartu Surat Keluar registration cards.
 */
class ExportLetterNumberRegistrationPdfService
{
    public function __construct(private RecordActivityLogService $activityLog) {}

    /**
     * @param  Collection<int, \App\Models\LetterNumberRegistration>  $registrations
     */
    public function handle(
        Collection $registrations,
        string $layout,
        string $background,
        string $backgroundColor,
    ): Response {
        $this->activityLog->record(
            action: 'export_pdf',
            module: 'letter_number_registration',
            description: sprintf('Pengguna mengekspor %d kartu surat keluar ke PDF.', $registrations->count()),
            properties: [
                'count' => $registrations->count(),
                'layout' => $layout,
                'background' => $background,
            ],
        );

        $fileName = sprintf('bapperida-kartu-surat-keluar-%s.pdf', now()->format('YmdHis'));

        $pdf = Pdf::loadView('letter-number-registrations.card-print', [
            'registrations' => $registrations,
            'layout' => $layout,
            'background' => $background,
            'backgroundColor' => $backgroundColor,
            'layoutLabel' => RegistrationCardPrint::layoutOptions()[$layout],
            'backgroundLabel' => RegistrationCardPrint::backgroundOptions()[$background],
            'pdfMode' => true,
        ])->setPaper(RegistrationCardPrint::cardPaperSize(), 'landscape');

        return $pdf->download($fileName);
    }
}
