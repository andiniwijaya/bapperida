<?php

namespace Tests\Concerns;

use App\Models\Department;
use App\Models\IncomingLetter;
use App\Models\User;

trait CreatesIncomingLetters
{
    protected function createIncomingLetter(
        User $creator,
        Department $department,
        array $overrides = [],
    ): IncomingLetter {
        $year = (int) ($overrides['year'] ?? 2026);

        $incomingLetter = new IncomingLetter(array_merge([
            'letter_number' => 'TEST/001/DPT/'.$year,
            'sent_date' => "{$year}-07-01",
            'received_date' => "{$year}-07-02",
            'disposition_date' => "{$year}-07-03",
            'sender' => 'Pengirim Test',
            'department_id' => $department->id,
            'disposition_department_id' => $department->id,
            'subject' => 'Perihal Surat',
            'agenda_name' => 'Agenda Surat',
            'summary' => 'Ringkasan surat masuk.',
            'letter_attribute' => 'regular',
            'attachment' => '2 Berkas',
            'file_path' => null,
            'notes' => 'Catatan internal',
        ], $overrides));

        $incomingLetter->status = $overrides['status'] ?? 'active';
        $incomingLetter->created_by = $creator->id;
        $incomingLetter->save();

        return $incomingLetter;
    }
}
