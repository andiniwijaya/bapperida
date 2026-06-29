<?php



namespace App\Services\IncomingLetter;



use App\Models\IncomingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use Illuminate\Support\Facades\Auth;



/**

 * Soft-deletes an incoming letter and records deleted_by audit field.

 *

 * Audit trail: records incoming letter deletion with target archive entity reference.

 *

 * Related modules: IncomingLetterPolicy, IncomingLetterController.

 */

class DeleteIncomingLetterService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Mark deleted_by and soft-delete the incoming letter.

     *

     * @param  IncomingLetter  $incomingLetter  Target archive.

     */

    public function handle(IncomingLetter $incomingLetter): void

    {

        $incomingLetter->deleted_by = Auth::id();

        $incomingLetter->save();



        $incomingLetter->delete();



        $this->activityLog->record(

            action: 'deleted',

            module: 'incoming_letter',

            description: sprintf('Arsip surat masuk nomor %s berhasil dihapus.', $incomingLetter->letter_number),

            entity: $incomingLetter,

        );

    }

}

