<?php

namespace App\Services\LetterNumberRegistration;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Services\SystemSetting\LetterNumberFormatter;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Validation\ValidationException;

/**
 * Computes preview letter numbers without persisting to the database.
 *
 * Business rules:
 * - Format driven by system_settings.letter_number_template via SystemConfigurationService.
 * - Auto-selects next gap in sequence when sequence_number omitted.
 * - Active year and start number come from system configuration.
 *
 * Configuration impact: letter number format changes apply without code deploy.
 */
class PreviewLetterNumberService
{
    public function __construct(
        private SystemConfigurationService $configuration,
        private LetterNumberFormatter $formatter,
    ) {}

    /**
     * Build sequence and formatted letter number for preview or persistence.
     *
     * @param  string  $letterCode  Letter type/code segment.
     * @param  int  $departmentId  Active department primary key.
     * @param  int|null  $sequenceNumber  Explicit sequence or null for next available.
     * @param  int|null  $year  Calendar year; defaults to configured active year.
     * @return array{sequence_number: int, letter_number: string}
     *
     * @throws ValidationException When department is invalid or inactive.
     */
    public function handle(
        string $letterCode,
        int $departmentId,
        ?int $sequenceNumber = null,
        ?int $year = null,
    ): array {
        $year ??= $this->configuration->activeYear();

        $department = Department::query()
            ->whereKey($departmentId)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->first();

        if (! $department) {
            throw ValidationException::withMessages([
                'department_id' => 'Bidang tidak valid atau tidak aktif.',
            ]);
        }

        $sequenceNumber ??= $this->nextAvailableSequenceNumber($year);

        $letterNumber = $this->formatter->format(
            $this->configuration->letterNumberTemplate(),
            [
                'letter_code' => $letterCode,
                'sequence_number' => $sequenceNumber,
                'department_code' => $department->code,
                'year' => $year,
                'prefix' => $this->configuration->letterPrefix(),
            ],
        );

        return [
            'sequence_number' => $sequenceNumber,
            'letter_number' => $letterNumber,
        ];
    }

    /**
     * Find the lowest unused sequence number for the given year (gap-fill algorithm).
     *
     * @param  int  $year  Calendar year to scan.
     * @return int Next available sequence starting from configured letter_start_number.
     */
    private function nextAvailableSequenceNumber(int $year): int
    {
        $usedSequenceNumbers = LetterNumberRegistration::query()
            ->where('year', $year)
            ->orderBy('sequence_number')
            ->pluck('sequence_number')
            ->toArray();

        $next = $this->configuration->letterStartNumber();

        foreach ($usedSequenceNumbers as $sequenceNumber) {
            if ($sequenceNumber !== $next) {
                return $next;
            }

            $next++;
        }

        return $next;
    }
}
