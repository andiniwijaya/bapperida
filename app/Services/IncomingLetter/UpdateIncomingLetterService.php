<?php



namespace App\Services\IncomingLetter;



use App\Models\IncomingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;



/**

 * Updates incoming letter archive fields and optional PDF replacement.

 *

 * Business rules:

 * - Replaces stored PDF when new file uploaded; deletes previous file.

 * - updated_by set explicitly outside mass assignment.

 *

 * Audit trail: records incoming letter update with target archive entity reference.

 *

 * Notification dispatch: incomingLetterUpdated after audit log.

 *

 * Related modules: IncomingLetterFileStorage, IncomingLetter.

 */

class UpdateIncomingLetterService

{

    public function __construct(

        protected IncomingLetterFileStorage $fileStorage,

        protected RecordActivityLogService $activityLog,

        protected NotificationService $notificationService,

    ) {}



    /**

     * Apply validated changes and optionally replace PDF file.

     *

     * @param  IncomingLetter  $incomingLetter  Target archive.

     * @param  array<string, mixed>  $data  Validated fields.

     * @param  UploadedFile|null  $file  Optional new PDF.

     * @return IncomingLetter Refreshed model after save.

     */

    public function handle(IncomingLetter $incomingLetter, array $data, ?UploadedFile $file): IncomingLetter

    {

        return DB::transaction(function () use ($incomingLetter, $data, $file) {

            if ($file) {

                $this->fileStorage->delete($incomingLetter->file_path);

                $incomingLetter->file_path = $this->fileStorage->store($file);

            }



            $incomingLetter->letter_number = $data['letter_number'];

            $incomingLetter->sent_date = $data['sent_date'];

            $incomingLetter->received_date = $data['received_date'];

            $incomingLetter->disposition_date = $data['disposition_date'] ?? null;

            $incomingLetter->sender = $data['sender'];

            $incomingLetter->department_id = (int) $data['department_id'];

            $incomingLetter->disposition_department_id = $data['disposition_department_id'] ?? null;

            $incomingLetter->subject = $data['subject'];

            $incomingLetter->agenda_name = $data['agenda_name'] ?? null;

            $incomingLetter->summary = $data['summary'] ?? null;

            $incomingLetter->letter_attribute = $data['letter_attribute'];

            $incomingLetter->attachment = $data['attachment'] ?? null;

            $incomingLetter->notes = $data['notes'] ?? null;

            $incomingLetter->status = $data['status'];

            $incomingLetter->updated_by = Auth::id();

            $incomingLetter->save();



            $incomingLetter = $incomingLetter->fresh();



            $this->activityLog->record(

                action: 'updated',

                module: 'incoming_letter',

                description: sprintf('Arsip surat masuk nomor %s berhasil diperbarui.', $incomingLetter->letter_number),

                entity: $incomingLetter,

            );

            $this->notificationService->incomingLetterUpdated(
                Auth::user(),
                $incomingLetter->letter_number,
            );

            return $incomingLetter;

        });

    }

}

