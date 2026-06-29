<?php



namespace App\Services\OutgoingLetter;



use App\Models\OutgoingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;



/**

 * Updates outgoing letter metadata, status, and optional PDF replacement.

 *

 * Audit trail: records outgoing letter update with target archive entity reference.

 *

 * Notification dispatch: outgoingLetterUpdated after audit log.

 *

 * Related modules: OutgoingLetterFileStorage, OutgoingLetter.

 */

class UpdateOutgoingLetterService

{

    public function __construct(

        protected OutgoingLetterFileStorage $fileStorage,

        protected RecordActivityLogService $activityLog,

        protected NotificationService $notificationService,

    ) {}



    /**

     * Apply updates; replace stored PDF when a new file is uploaded.

     *

     * @param  OutgoingLetter  $outgoingLetter  Target archive.

     * @param  array<string, mixed>  $data  Validated update fields including status.

     * @param  UploadedFile|null  $file  Optional replacement PDF.

     * @return OutgoingLetter Refreshed model.

     */

    public function handle(OutgoingLetter $outgoingLetter, array $data, ?UploadedFile $file): OutgoingLetter

    {

        return DB::transaction(function () use ($outgoingLetter, $data, $file) {

            if ($file) {

                $this->fileStorage->delete($outgoingLetter->file_path);

                $outgoingLetter->file_path = $this->fileStorage->store($file);

            }



            $outgoingLetter->letter_type = $data['letter_type'];

            $outgoingLetter->attachment = $data['attachment'] ?? null;

            $outgoingLetter->notes = $data['notes'] ?? null;

            $outgoingLetter->status = $data['status'];

            $outgoingLetter->updated_by = Auth::id();

            $outgoingLetter->save();



            $outgoingLetter = $outgoingLetter->fresh();



            $this->activityLog->record(

                action: 'updated',

                module: 'outgoing_letter',

                description: sprintf('Arsip surat keluar (ID %d) berhasil diperbarui.', $outgoingLetter->id),

                entity: $outgoingLetter,

            );

            $this->notificationService->outgoingLetterUpdated(
                Auth::user(),
                $outgoingLetter->id,
            );

            return $outgoingLetter;

        });

    }

}

