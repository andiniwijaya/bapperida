<?php

namespace App\Services\OutgoingLetter;

use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

/**
 * Ensures a letter number registration is eligible for outgoing letter creation.
 *
 * Business rules:
 * - Registration must be active, not deleted, and not already linked (including trashed).
 * - Uses lockForUpdate inside caller transaction for race-safe exclusivity.
 *
 * Related modules: LetterNumberRegistration, OutgoingLetter, StoreOutgoingLetterService.
 */
class OutgoingLetterRegistrationGuard
{
    /**
     * Load and lock registration; throw when unavailable for new outgoing letter.
     *
     * @param  int  $registrationId  Letter number registration primary key.
     * @return LetterNumberRegistration Locked active registration model.
     *
     * @throws ValidationException When registration invalid or already used.
     */
    public function ensureAvailableForOutgoing(int $registrationId): LetterNumberRegistration
    {
        $registration = LetterNumberRegistration::query()
            ->whereKey($registrationId)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->lockForUpdate()
            ->first();

        if (! $registration) {
            throw ValidationException::withMessages([
                'letter_number_registration_id' => 'Registrasi penomoran tidak valid atau tidak aktif.',
            ]);
        }

        if (OutgoingLetter::withTrashed()
            ->where('letter_number_registration_id', $registrationId)
            ->exists()) {
            throw ValidationException::withMessages([
                'letter_number_registration_id' => 'Registrasi penomoran sudah digunakan untuk arsip surat keluar.',
            ]);
        }

        return $registration;
    }

    /**
     * Map unique constraint on letter_number_registration_id to validation error.
     *
     * @param  UniqueConstraintViolationException  $exception  Caught DB exception.
     *
     * @throws ValidationException Always re-thrown as validation error.
     */
    public function translateUniqueViolation(UniqueConstraintViolationException $exception): void
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'letter_number_registration_id')) {
            throw ValidationException::withMessages([
                'letter_number_registration_id' => 'Registrasi penomoran sudah digunakan untuk arsip surat keluar.',
            ]);
        }

        throw ValidationException::withMessages([
            'letter_number_registration_id' => 'Arsip surat keluar tidak dapat disimpan karena konflik data.',
        ]);
    }
}
