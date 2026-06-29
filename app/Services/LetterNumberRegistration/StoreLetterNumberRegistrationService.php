<?php



namespace App\Services\LetterNumberRegistration;



use App\Models\LetterNumberRegistration;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Database\UniqueConstraintViolationException;

use Illuminate\Support\Facades\Auth;



/**

 * Persists a new letter number registration with concurrency-safe sequence allocation.

 *

 * Business rules:

 * - Uses per-year MySQL advisory lock (or DB transaction fallback).

 * - Validates sequence and letter_number uniqueness before insert.

 * - Sets status active and created_by from authenticated user.

 *

 * Audit trail: records registration creation with new registration entity reference.

 *

 * Notification dispatch: letterRegistrationCreated after audit log.

 *

 * Related modules: PreviewLetterNumberService, LetterNumberSequenceGuard, LetterNumberRegistration.

 */

class StoreLetterNumberRegistrationService

{

    public function __construct(

        protected PreviewLetterNumberService $previewService,

        protected LetterNumberSequenceGuard $sequenceGuard,

        protected RecordActivityLogService $activityLog,

        protected NotificationService $notificationService,

    ) {}



    /**

     * Create registration inside a year-scoped lock and transaction.

     *

     * @param  array<string, mixed>  $data  Validated registration fields.

     * @return LetterNumberRegistration Newly persisted registration.

     */

    public function handle(array $data): LetterNumberRegistration

    {

        $year = (int) ($data['year'] ?? now()->year);

        $sequenceNumber = (int) $data['sequence_number'];



        return $this->sequenceGuard->withYearLock($year, function () use ($data, $year, $sequenceNumber) {

            $this->sequenceGuard->ensureSequenceAvailable($year, $sequenceNumber);



            $preview = $this->previewService->handle(

                letterCode: $data['letter_code'],

                departmentId: (int) $data['department_id'],

                sequenceNumber: $sequenceNumber,

                year: $year,

            );



            $this->sequenceGuard->ensureLetterNumberAvailable($preview['letter_number']);



            try {

                $registration = new LetterNumberRegistration([

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



                $registration->status = 'active';

                $registration->created_by = Auth::id();

                $registration->save();



                $this->activityLog->record(

                    action: 'registration_created',

                    module: 'letter_number_registration',

                    description: sprintf('Registrasi nomor surat %s berhasil dibuat.', $registration->letter_number),

                    entity: $registration,

                );

                $this->notificationService->letterRegistrationCreated(
                    Auth::user(),
                    $registration->letter_number,
                );

                return $registration;

            } catch (UniqueConstraintViolationException $exception) {

                $this->sequenceGuard->translateUniqueViolation($exception);

            }

        });

    }

}

