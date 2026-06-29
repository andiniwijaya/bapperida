<?php

namespace Tests\Concerns;

use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\User;

trait CreatesLetterNumberRegistrations
{
    protected function createLetterNumberRegistration(
        User $creator,
        Department $department,
        array $overrides = [],
    ): LetterNumberRegistration {
        $sequenceNumber = (int) ($overrides['sequence_number'] ?? 1);
        $letterCode = (string) ($overrides['letter_code'] ?? 'TEST');
        $year = (int) ($overrides['year'] ?? 2026);

        $registration = new LetterNumberRegistration(array_merge([
            'index_code' => 'IDX-001',
            'letter_code' => $letterCode,
            'sequence_number' => $sequenceNumber,
            'year' => $year,
            'letter_number' => sprintf('%s/%03d/%s/%s', $letterCode, $sequenceNumber, $department->code, $year),
            'subject' => 'Surat Uji',
            'summary' => 'Ringkasan uji',
            'recipient' => 'Direktur',
            'letter_date' => '2026-07-01',
            'letter_type' => 'regular',
            'attachment' => 'Tidak ada',
            'notes' => 'Catatan uji',
            'department_id' => $department->id,
        ], $overrides));

        $registration->status = 'active';
        $registration->created_by = $creator->id;
        $registration->save();

        return $registration;
    }
}
