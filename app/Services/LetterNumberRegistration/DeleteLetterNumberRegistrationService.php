<?php



namespace App\Services\LetterNumberRegistration;



use App\Models\LetterNumberRegistration;

use App\Services\ActivityLog\RecordActivityLogService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



/**

 * Soft-deletes a registration when not linked to an outgoing letter.

 *

 * Audit trail: records registration deletion with target registration entity reference.

 *

 * Related modules: LetterNumberRegistration, OutgoingLetter.

 */

class DeleteLetterNumberRegistrationService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Record deleter and soft-delete unless outgoing letter exists.

     *

     * @param  LetterNumberRegistration  $registration  Target record.

     *

     * @throws ValidationException When linked to outgoing letter archive.

     */

    public function handle(LetterNumberRegistration $registration): void

    {

        if ($registration->hasOutgoingLetter()) {

            throw ValidationException::withMessages([

                'registration' => 'Registrasi tidak dapat dihapus karena sudah terhubung dengan arsip surat keluar.',

            ]);

        }



        $registration->deleted_by = Auth::id();

        $registration->save();



        $registration->delete();



        $this->activityLog->record(

            action: 'registration_deleted',

            module: 'letter_number_registration',

            description: sprintf('Registrasi nomor surat %s berhasil dihapus.', $registration->letter_number),

            entity: $registration,

        );

    }

}

