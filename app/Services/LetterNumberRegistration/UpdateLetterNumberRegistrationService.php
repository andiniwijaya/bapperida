<?php

namespace App\Services\LetterNumberRegistration;

use App\Models\LetterNumberRegistration;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Updates letter number registration with numbering change protection.
 *
 * Business rules:
 * - Blocks numbering field changes when hasOutgoingLetter() is true.
 * - Re-validates sequence and letter_number under year lock.
 *
 * Audit trail: records registration update with target registration entity reference.
 *
 * Notification dispatch: letterRegistrationUpdated after audit log.
 *
 * Related modules: PreviewLetterNumberService, LetterNumberSequenceGuard, OutgoingLetter.
 */
class UpdateLetterNumberRegistrationService
{
    public function __construct(
        protected PreviewLetterNumberService $previewService,
        protected LetterNumberSequenceGuard $sequenceGuard,
        protected RecordActivityLogService $activityLog,
        protected NotificationService $notificationService,
    ) {}

    /**
     * Apply updates with sequence guard and outgoing-letter constraint.
     *
     * @param  LetterNumberRegistration  $registration  Target record.
     * @param  array<string, mixed>  $data  Validated update fields.
     * @return LetterNumberRegistration Refreshed model.
     *
     * @throws ValidationException When numbering change blocked by outgoing letter link.
     */
    public function handle(
        LetterNumberRegistration $registration,
        array $data
    ): LetterNumberRegistration {
        $year = (int) ($data['year'] ?? $registration->year);
        $sequenceNumber = (int) ($data['sequence_number'] ?? $registration->sequence_number);

        $numberingChanged = $this->numberingFieldsChanged(
            $registration,
            $data,
            $year,
            $sequenceNumber
        );

        if ($numberingChanged && $registration->hasOutgoingLetter()) {
            throw ValidationException::withMessages([
                'letter_number' => 'Nomor surat tidak dapat diubah karena sudah terhubung dengan arsip surat keluar.',
            ]);
        }

        return $this->sequenceGuard->withYearLock($year, function () use ($registration, $data, $year, $sequenceNumber) {
            $this->sequenceGuard->ensureSequenceAvailable(
                $year,
                $sequenceNumber,
                $registration->id
            );

            $preview = $this->previewService->handle(
                letterCode: $data['letter_code'],
                departmentId: (int) $data['department_id'],
                sequenceNumber: $sequenceNumber,
                year: $year,
            );

            $this->sequenceGuard->ensureLetterNumberAvailable(
                $preview['letter_number'],
                $registration->id
            );

            try {
                $registration->fill([
                    'index_code' => $data['index_code'],
                    'letter_code' => $data['letter_code'],
                    'sequence_number' => $preview['sequence_number'],
                    'year' => $year,
                    'letter_number' => $preview['letter_number'],
                    'subject' => $data['subject'],
                    'summary' => $data['summary'] ?? null,
                    'recipient' => $data['recipient'],
                    'letter_date' => $data['letter_date'],
                    'letter_type' => $data['letter_type'],
                    'attachment' => $data['attachment'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'department_id' => (int) $data['department_id'],
                ]);

                $registration->updated_by = Auth::id();
                $registration->save();

                $registration = $registration->fresh();

                $this->activityLog->record(
                    action: 'registration_updated',
                    module: 'letter_number_registration',
                    description: sprintf('Registrasi nomor surat %s berhasil diperbarui.', $registration->letter_number),
                    entity: $registration,
                );

                $this->notificationService->letterRegistrationUpdated(
                    Auth::user(),
                    $registration->letter_number,
                );

                return $registration;
            } catch (UniqueConstraintViolationException $exception) {
                $this->sequenceGuard->translateUniqueViolation($exception);
            }
        });
    }

    /**
     * Detect whether letter_code, department, year, or sequence changed.
     *
     * @param  array<string, mixed>  $data  Incoming update payload.
     */
    private function numberingFieldsChanged(
        LetterNumberRegistration $registration,
        array $data,
        int $year,
        int $sequenceNumber
    ): bool {
        return $data['letter_code'] !== $registration->letter_code
            || (int) $data['department_id'] !== (int) $registration->department_id
            || $year !== (int) $registration->year
            || $sequenceNumber !== (int) $registration->sequence_number;
    }
}
