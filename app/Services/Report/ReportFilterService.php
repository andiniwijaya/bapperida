<?php

namespace App\Services\Report;

use App\Models\Department;
use App\Models\IncomingLetter;
use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use Illuminate\Support\Facades\DB;

/**
 * Supplies filter metadata for report UIs (years, departments, report types).
 *
 * Business rules:
 * - Years are derived from distinct letter dates across all modules.
 * - Departments list only active, non-deleted units.
 *
 * Related modules: ReportController (filters endpoint).
 */
class ReportFilterService
{
    /**
     * @return array{report_types: array<int, array{value: string, label: string}>, years: \Illuminate\Support\Collection, departments: \Illuminate\Database\Eloquent\Collection}
     */
    public function handle(): array
    {
        $departments = Department::query()
            ->active()
            ->select(['id', 'code', 'name'])
            ->orderBy('name')
            ->get();

        $registrationYears = LetterNumberRegistration::query()
            ->select('year')
            ->distinct()
            ->pluck('year');

        $incomingYears = IncomingLetter::query()
            ->selectRaw($this->yearSelectExpression('received_date'))
            ->pluck('year');

        $outgoingYears = OutgoingLetter::query()
            ->join('letter_number_registrations', 'outgoing_letters.letter_number_registration_id', '=', 'letter_number_registrations.id')
            ->selectRaw($this->yearSelectExpression('letter_number_registrations.letter_date'))
            ->pluck('year');

        $years = $registrationYears
            ->concat($incomingYears)
            ->concat($outgoingYears)
            ->unique()
            ->sortDesc()
            ->values();

        return [
            'report_types' => [
                ['value' => 'all', 'label' => 'Semua'],
                ['value' => 'registration', 'label' => 'Registrasi Penomoran'],
                ['value' => 'incoming', 'label' => 'Arsip Surat Masuk'],
                ['value' => 'outgoing', 'label' => 'Arsip Surat Keluar'],
            ],
            'years' => $years,
            'departments' => $departments,
        ];
    }

  /**
     * Cross-driver year extraction for date columns.
     */
    private function yearSelectExpression(string $column): string
    {
        $expression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y', {$column})"
            : "year({$column})";

        return "distinct {$expression} as year";
    }
}
