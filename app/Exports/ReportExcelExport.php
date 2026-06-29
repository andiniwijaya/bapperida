<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ReportExcelExport implements FromView, WithEvents
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<int, array{key: string, label: string}>  $exportColumns
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        protected Collection $rows,
        protected string $reportTypeLabel,
        protected array $exportColumns,
        protected array $filters,
        protected string $printedBy,
        protected \Carbon\CarbonInterface $printedAt,
        protected array $reportBranding,
    ) {}

    public function view(): View
    {
        return view('reports.excel', [
            'rows' => $this->rows,
            'reportTypeLabel' => $this->reportTypeLabel,
            'exportColumns' => $this->exportColumns,
            'filters' => $this->filters,
            'printedBy' => $this->printedBy,
            'printedAt' => $this->printedAt,
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
                $columnCount = count($this->exportColumns) + 1;
                $lastColumn = $this->columnLetter($columnCount);
                $sheet->freezePane('A5');
                $sheet->setAutoFilter("A4:{$lastColumn}4");

                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
