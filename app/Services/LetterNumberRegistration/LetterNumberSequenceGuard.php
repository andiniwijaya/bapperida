<?php

namespace App\Services\LetterNumberRegistration;

use App\Models\LetterNumberRegistration;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Concurrency and uniqueness guard for letter number sequence allocation.
 *
 * Business rules:
 * - MySQL: GET_LOCK per calendar year during store/update/restore.
 * - Non-MySQL: falls back to database transaction without advisory lock.
 * - Translates unique constraint violations to validation errors.
 *
 * Related modules: Store/Update/Restore LetterNumberRegistration services.
 */
class LetterNumberSequenceGuard
{
    /**
     * Execute callback inside a year-scoped lock (MySQL) or transaction.
     *
     * @param  int  $year  Calendar year for lock scope.
     * @param  callable  $callback  Business logic to run atomically.
     * @return mixed Callback return value.
     *
     * @throws ValidationException When MySQL lock cannot be acquired within 10 seconds.
     */
    public function withYearLock(int $year, callable $callback): mixed
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return $this->withMysqlYearLock($year, $callback);
        }

        return DB::transaction($callback);
    }

    /**
     * Acquire MySQL named lock, run callback in transaction, then release lock.
     *
     * @param  int  $year  Lock namespace year.
     * @param  callable  $callback  Atomic registration logic.
     * @return mixed
     */
    private function withMysqlYearLock(int $year, callable $callback): mixed
    {
        $lockName = "letter_number_registration_year_{$year}";

        $acquired = DB::selectOne('SELECT GET_LOCK(?, 10) as acquired', [$lockName]);

        if (! $acquired || (int) $acquired->acquired !== 1) {
            throw ValidationException::withMessages([
                'sequence_number' => 'Sistem sedang memproses nomor surat. Silakan coba lagi.',
            ]);
        }

        try {
            return DB::transaction($callback);
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) as released', [$lockName]);
        }
    }

    /**
     * Assert sequence_number is not already used for the year.
     *
     * @param  int  $year  Calendar year.
     * @param  int  $sequenceNumber  Candidate sequence.
     * @param  int|null  $ignoreRegistrationId  Exclude current record on update/restore.
     *
     * @throws ValidationException When sequence is taken.
     */
    public function ensureSequenceAvailable(int $year, int $sequenceNumber, ?int $ignoreRegistrationId = null): void
    {
        $exists = LetterNumberRegistration::query()
            ->where('year', $year)
            ->where('sequence_number', $sequenceNumber)
            ->when(
                $ignoreRegistrationId,
                fn ($query) => $query->whereKeyNot($ignoreRegistrationId)
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'sequence_number' => 'Nomor urut sudah digunakan untuk tahun tersebut.',
            ]);
        }
    }

    /**
     * Assert formatted letter_number is not already used globally.
     *
     * @param  string  $letterNumber  Full formatted number string.
     * @param  int|null  $ignoreRegistrationId  Exclude current record on update/restore.
     *
     * @throws ValidationException When letter number is taken.
     */
    public function ensureLetterNumberAvailable(string $letterNumber, ?int $ignoreRegistrationId = null): void
    {
        $exists = LetterNumberRegistration::query()
            ->where('letter_number', $letterNumber)
            ->when(
                $ignoreRegistrationId,
                fn ($query) => $query->whereKeyNot($ignoreRegistrationId)
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'letter_number' => 'Nomor surat sudah digunakan.',
            ]);
        }
    }

    /**
     * Map database unique constraint errors to user-facing validation messages.
     *
     * @param  UniqueConstraintViolationException  $exception  Caught DB exception.
     *
     * @throws ValidationException Always re-thrown as validation error.
     */
    public function translateUniqueViolation(UniqueConstraintViolationException $exception): void
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'sequence_number') || str_contains($message, 'letter_number_registrations_sequence_number_year')) {
            throw ValidationException::withMessages([
                'sequence_number' => 'Nomor urut sudah digunakan untuk tahun tersebut.',
            ]);
        }

        if (str_contains($message, 'letter_number')) {
            throw ValidationException::withMessages([
                'letter_number' => 'Nomor surat sudah digunakan.',
            ]);
        }

        throw ValidationException::withMessages([
            'sequence_number' => 'Nomor surat tidak dapat disimpan karena konflik data.',
        ]);
    }
}
