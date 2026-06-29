<?php



namespace App\Services\IncomingLetter;



use App\Models\IncomingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Database\UniqueConstraintViolationException;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;



/**

 * Creates incoming letter archive with PDF upload.

 *

 * Business rules:

 * - Letter number is external; uniqueness enforced at validation and DB layer.

 * - Sets status and created_by explicitly outside mass assignment.

 *

 * Audit trail: records incoming letter creation with new archive entity reference.

 *

 * Notification dispatch: incomingLetterCreated after audit log.

 *

 * Related modules: IncomingLetterFileStorage, IncomingLetter, Department.

 */

class StoreIncomingLetterService

{

    public function __construct(

        protected IncomingLetterFileStorage $fileStorage,

        protected RecordActivityLogService $activityLog,

        protected NotificationService $notificationService,

    ) {}



    /**

     * Store PDF and create incoming letter inside a transaction.

     *

     * @param  array<string, mixed>  $data  Validated archive fields.

     * @param  UploadedFile  $file  Required PDF upload.

     * @return IncomingLetter Newly created archive.

     */

    public function handle(array $data, UploadedFile $file): IncomingLetter

    {

        return DB::transaction(function () use ($data, $file) {

            $path = $this->fileStorage->store($file);



            try {

                $incomingLetter = new IncomingLetter([

                    'letter_number' => $data['letter_number'],

                    'sent_date' => $data['sent_date'],

                    'received_date' => $data['received_date'],

                    'disposition_date' => $data['disposition_date'] ?? null,

                    'sender' => $data['sender'],

                    'department_id' => (int) $data['department_id'],

                    'disposition_department_id' => $data['disposition_department_id'] ?? null,

                    'subject' => $data['subject'],

                    'agenda_name' => $data['agenda_name'] ?? null,

                    'summary' => $data['summary'] ?? null,

                    'letter_attribute' => $data['letter_attribute'],

                    'attachment' => $data['attachment'] ?? null,

                    'file_path' => $path,

                    'notes' => $data['notes'] ?? null,

                ]);



                $incomingLetter->status = $data['status'];

                $incomingLetter->created_by = Auth::id();

                $incomingLetter->save();



                $this->activityLog->record(

                    action: 'created',

                    module: 'incoming_letter',

                    description: sprintf('Arsip surat masuk nomor %s berhasil dibuat.', $incomingLetter->letter_number),

                    entity: $incomingLetter,

                );

                $this->notificationService->incomingLetterCreated(
                    Auth::user(),
                    $incomingLetter->letter_number,
                );

                return $incomingLetter;

            } catch (UniqueConstraintViolationException $exception) {

                $this->fileStorage->delete($path);



                if (str_contains(strtolower($exception->getMessage()), 'letter_number')) {

                    throw ValidationException::withMessages([

                        'letter_number' => 'Nomor surat sudah digunakan untuk arsip surat masuk aktif.',

                    ]);

                }



                throw ValidationException::withMessages([

                    'letter_number' => 'Arsip surat masuk tidak dapat disimpan karena konflik data.',

                ]);

            }

        });

    }

}

