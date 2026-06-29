<?php

namespace App\Services\IncomingLetter;

use App\Models\IncomingLetter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Secure download of incoming letter PDF with audit trail entry.
 *
 * Audit trail: records file download with letter entity reference.
 */
class DownloadIncomingLetterFileService
{
    public function __construct(
        private IncomingLetterFileStorage $fileStorage,
        private \App\Services\ActivityLog\RecordActivityLogService $activityLog,
    ) {}

    /**
     * @return BinaryFileResponse
     */
    public function handle(IncomingLetter $incomingLetter)
    {
        if (! $incomingLetter->file_path || ! Storage::exists($incomingLetter->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $this->activityLog->record(
            action: 'file_downloaded',
            module: 'incoming_letter',
            description: sprintf(
                'Pengguna mengunduh file arsip surat masuk nomor %s (ID %d).',
                $incomingLetter->letter_number,
                $incomingLetter->id
            ),
            entity: $incomingLetter,
        );

        return response()->download(
            Storage::path($incomingLetter->file_path),
            $this->fileStorage->downloadName(
                $incomingLetter->file_path,
                $incomingLetter->letter_number
            )
        );
    }
}
