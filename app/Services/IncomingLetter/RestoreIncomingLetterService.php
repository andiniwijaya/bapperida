<?php



namespace App\Services\IncomingLetter;



use App\Models\IncomingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



/**

 * Restores soft-deleted incoming letter when letter number is not conflicting.

 *

 * Business rules:

 * - Clears deleted_by after restore.

 * - Blocks restore when another active letter uses the same letter_number.

 *

 * Audit trail: records incoming letter restoration with target archive entity reference.

 *

 * Notification dispatch: incomingLetterRestored after audit log.

 *

 * Related modules: IncomingLetter, IncomingLetterController.

 */

class RestoreIncomingLetterService

{

    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}



    /**

     * Restore trashed incoming letter after conflict check.

     *

     * @param  IncomingLetter  $incomingLetter  Trashed archive to restore.

     * @return IncomingLetter Restored model.

     *

     * @throws ValidationException When not trashed or letter number conflicts.

     */

    public function handle(IncomingLetter $incomingLetter): IncomingLetter

    {

        if (! $incomingLetter->trashed()) {

            throw ValidationException::withMessages([

                'incoming_letter' => 'Arsip surat masuk tidak dalam status terhapus.',

            ]);

        }



        $conflictExists = IncomingLetter::query()

            ->where('letter_number', $incomingLetter->letter_number)

            ->whereKeyNot($incomingLetter->id)

            ->exists();



        if ($conflictExists) {

            throw ValidationException::withMessages([

                'incoming_letter' => 'Nomor surat sudah digunakan oleh arsip surat masuk aktif lain.',

            ]);

        }



        $incomingLetter->restore();



        $incomingLetter->deleted_by = null;

        $incomingLetter->save();



        $incomingLetter = $incomingLetter->fresh();



        $this->activityLog->record(

            action: 'restored',

            module: 'incoming_letter',

            description: sprintf('Arsip surat masuk nomor %s berhasil dipulihkan.', $incomingLetter->letter_number),

            entity: $incomingLetter,

        );

        $this->notificationService->incomingLetterRestored(
            Auth::user(),
            $incomingLetter->letter_number,
        );

        return $incomingLetter;

    }

}

