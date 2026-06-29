<?php



namespace App\Services\LetterNumberRegistration;



use App\Models\LetterNumberRegistration;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



/**

 * Restores a trashed registration after re-validating sequence uniqueness.

 *

 * Audit trail: records registration restoration with target registration entity reference.

 *

 * Notification dispatch: letterRegistrationRestored after audit log.

 *

 * Related modules: LetterNumberSequenceGuard, LetterNumberRegistration.

 */

class RestoreLetterNumberRegistrationService

{

    public function __construct(

        protected LetterNumberSequenceGuard $sequenceGuard,

        protected RecordActivityLogService $activityLog,

        protected NotificationService $notificationService,

    ) {}



    /**

     * Restore registration under year lock when sequence and letter_number are free.

     *

     * @param  LetterNumberRegistration  $registration  Trashed registration.

     * @return LetterNumberRegistration Refreshed active record.

     *

     * @throws ValidationException When not trashed or uniqueness conflict.

     */

    public function handle(LetterNumberRegistration $registration): LetterNumberRegistration

    {

        if (! $registration->trashed()) {

            throw ValidationException::withMessages([

                'registration' => 'Registrasi tidak dalam status terhapus.',

            ]);

        }



        return $this->sequenceGuard->withYearLock((int) $registration->year, function () use ($registration) {

            $this->sequenceGuard->ensureSequenceAvailable(

                (int) $registration->year,

                (int) $registration->sequence_number,

                $registration->id

            );



            $this->sequenceGuard->ensureLetterNumberAvailable(

                $registration->letter_number,

                $registration->id

            );



            $registration->restore();



            $registration->deleted_by = null;

            $registration->save();



            $registration = $registration->fresh();



            $this->activityLog->record(

                action: 'registration_restored',

                module: 'letter_number_registration',

                description: sprintf('Registrasi nomor surat %s berhasil dipulihkan.', $registration->letter_number),

                entity: $registration,

            );

            $this->notificationService->letterRegistrationRestored(
                Auth::user(),
                $registration->letter_number,
            );

            return $registration;

        });

    }

}

