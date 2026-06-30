<?php

namespace App\Services\LetterNumberRegistration;

use App\Models\Department;
use App\Services\SystemSetting\LetterNumberFormatter;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Validation\ValidationException;

/**
 * Computes preview letter numbers without persisting to the database.
 *
 * Business rules:
 * - Format driven by system_settings.letter_number_template via SystemConfigurationService.
 * - sequence_number is supplied manually by the user.
 * - Active year comes from system configuration when omitted.
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
     * @param  int  $sequenceNumber  User-supplied sequence for the year.
     * @param  int|null  $year  Calendar year; defaults to configured active year.
     * @return array{sequence_number: int, letter_number: string}
     *
     * @throws ValidationException When department is invalid or inactive.
     */
    public function handle(
        string $letterCode,
        int $departmentId,
        int $sequenceNumber,
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
}
