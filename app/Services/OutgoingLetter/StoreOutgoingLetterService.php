<?php



namespace App\Services\OutgoingLetter;



use App\Models\OutgoingLetter;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Database\UniqueConstraintViolationException;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;



/**

 * Creates outgoing letter archive with registration exclusivity lock.

 *

 * Business rules:

 * - One outgoing letter per registration (enforced by guard and unique constraint).

 * - Rolls back uploaded file on unique constraint failure.

 * - Sets status active and created_by from authenticated user.

 *

 * Audit trail: records outgoing letter creation with new archive entity reference.

 *

 * Notification dispatch: outgoingLetterCreated after audit log.

 *

 * Related modules: OutgoingLetterRegistrationGuard, OutgoingLetterFileStorage, LetterNumberRegistration.

 */

class StoreOutgoingLetterService

{

    public function __construct(

        protected OutgoingLetterRegistrationGuard $registrationGuard,

        protected OutgoingLetterFileStorage $fileStorage,

        protected RecordActivityLogService $activityLog,

        protected NotificationService $notificationService,

    ) {}



    /**

     * Store PDF and create outgoing letter inside a transaction.

     *

     * @param  array<string, mixed>  $data  Validated archive fields.

     * @param  UploadedFile  $file  Required PDF upload.

     * @return OutgoingLetter Newly created archive.

     */

    public function handle(array $data, UploadedFile $file): OutgoingLetter

    {

        return DB::transaction(function () use ($data, $file) {

            $this->registrationGuard->ensureAvailableForOutgoing(

                (int) $data['letter_number_registration_id']

            );



            $path = $this->fileStorage->store($file);



            try {

                $outgoingLetter = new OutgoingLetter([

                    'letter_number_registration_id' => (int) $data['letter_number_registration_id'],

                    'letter_type' => $data['letter_type'],

                    'attachment' => $data['attachment'] ?? null,

                    'file_path' => $path,

                    'notes' => $data['notes'] ?? null,

                ]);



                $outgoingLetter->status = 'active';

                $outgoingLetter->created_by = Auth::id();

                $outgoingLetter->save();



                $this->activityLog->record(

                    action: 'created',

                    module: 'outgoing_letter',

                    description: sprintf('Arsip surat keluar (ID %d) berhasil dibuat.', $outgoingLetter->id),

                    entity: $outgoingLetter,

                );

                $this->notificationService->outgoingLetterCreated(
                    Auth::user(),
                    $outgoingLetter->id,
                );

                return $outgoingLetter;

            } catch (UniqueConstraintViolationException $exception) {

                $this->fileStorage->delete($path);

                $this->registrationGuard->translateUniqueViolation($exception);

            }

        });

    }

}

