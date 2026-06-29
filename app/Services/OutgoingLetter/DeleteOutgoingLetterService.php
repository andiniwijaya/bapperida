<?php



namespace App\Services\OutgoingLetter;



use App\Models\OutgoingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use Illuminate\Support\Facades\Auth;



/**

 * Soft-deletes an outgoing letter archive with audit trail.

 *

 * Audit trail: records outgoing letter deletion with target archive entity reference.

 *

 * Related modules: OutgoingLetter, OutgoingLetterController.

 */

class DeleteOutgoingLetterService

{

    public function __construct(private RecordActivityLogService $activityLog) {}



    /**

     * Record deleter and soft-delete the archive.

     *

     * @param  OutgoingLetter  $outgoingLetter  Target record.

     */

    public function handle(OutgoingLetter $outgoingLetter): void

    {

        $outgoingLetter->deleted_by = Auth::id();

        $outgoingLetter->save();



        $outgoingLetter->delete();



        $this->activityLog->record(

            action: 'deleted',

            module: 'outgoing_letter',

            description: sprintf('Arsip surat keluar (ID %d) berhasil dihapus.', $outgoingLetter->id),

            entity: $outgoingLetter,

        );

    }

}

