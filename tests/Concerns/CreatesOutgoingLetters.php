<?php

namespace Tests\Concerns;

use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use App\Models\User;

trait CreatesOutgoingLetters
{
    protected function createOutgoingLetter(
        User $creator,
        LetterNumberRegistration $registration,
        array $overrides = [],
    ): OutgoingLetter {
        $outgoingLetter = new OutgoingLetter(array_merge([
            'letter_number_registration_id' => $registration->id,
            'letter_type' => 'regular',
            'attachment' => '2 Berkas',
            'file_path' => null,
            'notes' => 'Catatan surat',
        ], $overrides));

        $outgoingLetter->status = $overrides['status'] ?? 'active';
        $outgoingLetter->created_by = $creator->id;
        $outgoingLetter->save();

        return $outgoingLetter;
    }
}
