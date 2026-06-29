<?php

namespace App\Services\LetterNumberRegistration;

use App\Models\LetterNumberRegistration;

/**
 * Returns unused sequence numbers for a year (form dropdown helper).
 *
 * Scans from 1 through max(used)+5 and lists gaps.
 *
 * Related modules: LetterNumberRegistrationController, PreviewLetterNumberService.
 */
class AvailableSequenceService
{
    /**
     * List available sequence integers for the given calendar year.
     *
     * @param  int|null  $year  Defaults to current year when null.
     * @return array<int, int>
     */
    public function handle(?int $year = null): array
    {
        $year ??= now()->year;

        $used = LetterNumberRegistration::query()
            ->where('year', $year)
            ->orderBy('sequence_number')
            ->pluck('sequence_number')
            ->toArray();

        $available = [];

        $max = empty($used)
            ? 0
            : max($used);

        for ($i = 1; $i <= $max + 5; $i++) {
            if (! in_array($i, $used, true)) {
                $available[] = $i;
            }
        }

        return $available;
    }
}
