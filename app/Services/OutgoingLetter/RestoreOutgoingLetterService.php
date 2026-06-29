<?php



namespace App\Services\OutgoingLetter;



use App\Models\OutgoingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



/**

 * Restores a soft-deleted outgoing letter when registration is not taken.

 *

 * Business rules:

 * - Blocks restore when another active outgoing letter uses the same registration.

 *

 * Audit trail: records outgoing letter restoration with target archive entity reference.

 *

 * Notification dispatch: outgoingLetterRestored after audit log.

 *

 * Related modules: OutgoingLetter, LetterNumberRegistration.

 */

class RestoreOutgoingLetterService

{

    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}



    /**

     * Restore archive and clear deleted_by when no registration conflict exists.

     *

     * @param  OutgoingLetter  $outgoingLetter  Trashed archive.

     * @return OutgoingLetter Refreshed active record.

     *

     * @throws ValidationException When not trashed or registration conflict exists.

     */

    public function handle(OutgoingLetter $outgoingLetter): OutgoingLetter

    {

        if (! $outgoingLetter->trashed()) {

            throw ValidationException::withMessages([

                'outgoing_letter' => 'Arsip surat keluar tidak dalam status terhapus.',

            ]);

        }



        $conflictExists = OutgoingLetter::query()

            ->where('letter_number_registration_id', $outgoingLetter->letter_number_registration_id)

            ->whereKeyNot($outgoingLetter->id)

            ->exists();



        if ($conflictExists) {

            throw ValidationException::withMessages([

                'outgoing_letter' => 'Registrasi penomoran sudah digunakan oleh arsip surat keluar aktif lain.',

            ]);

        }



        $outgoingLetter->restore();



        $outgoingLetter->deleted_by = null;

        $outgoingLetter->save();



        $outgoingLetter = $outgoingLetter->fresh();



        $this->activityLog->record(

            action: 'restored',

            module: 'outgoing_letter',

            description: sprintf('Arsip surat keluar (ID %d) berhasil dipulihkan.', $outgoingLetter->id),

            entity: $outgoingLetter,

        );

        $this->notificationService->outgoingLetterRestored(
            Auth::user(),
            $outgoingLetter->id,
        );

        return $outgoingLetter;

    }

}

