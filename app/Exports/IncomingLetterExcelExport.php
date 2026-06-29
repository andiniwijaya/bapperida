<?php

namespace App\Exports;

use App\Models\IncomingLetter;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Excel export view for incoming letter archives with official report header.
 */
class IncomingLetterExcelExport implements FromView, WithEvents
{
    /**
     * @param  Collection<int, IncomingLetter>  $incomingLetters
     */
    public function __construct(
        protected Collection $incomingLetters,
        protected string $printedBy,
        protected \Carbon\CarbonInterface $printedAt,
        protected string $periodLabel,
        protected array $reportBranding,
    ) {}

    public function view(): View
    {
        return view('exports.incoming-letters-excel', [
            'incomingLetters' => $this->incomingLetters,
            'printedBy' => $this->printedBy,
            'printedAt' => $this->printedAt,
            'periodLabel' => $this->periodLabel,
            'reportBranding' => $this->reportBranding,
        ]);
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'P';
                $sheet->freezePane('A5');
                $sheet->setAutoFilter("A4:{$lastColumn}4");

                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * @param  Collection<int, IncomingLetter>  $incomingLetters
     */
    public static function make(Collection $incomingLetters, string $printedBy): self
    {
        $configuration = app(SystemConfigurationService::class);

        return new self(
            incomingLetters: $incomingLetters,
            printedBy: $printedBy,
            printedAt: now(),
            periodLabel: request('year') ? 'Tahun '.request('year') : 'Semua periode',
            reportBranding: $configuration->reportBranding(),
        );
    }
}
