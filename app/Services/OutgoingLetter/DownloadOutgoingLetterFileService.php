<?php

namespace App\Services\OutgoingLetter;

use App\Models\OutgoingLetter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Secure download of outgoing letter PDF with audit trail entry.
 *
 * Audit trail: records file download with letter entity reference.
 */
class DownloadOutgoingLetterFileService
{
    public function __construct(
        private OutgoingLetterFileStorage $fileStorage,
        private \App\Services\ActivityLog\RecordActivityLogService $activityLog,
    ) {}

    /**
     * @return BinaryFileResponse
     */
    public function handle(OutgoingLetter $outgoingLetter)
    {
        if (! $outgoingLetter->file_path || ! Storage::exists($outgoingLetter->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $this->activityLog->record(
            action: 'file_downloaded',
            module: 'outgoing_letter',
            description: sprintf(
                'Pengguna mengunduh file arsip surat keluar (ID %d, registrasi %d).',
                $outgoingLetter->id,
                $outgoingLetter->letter_number_registration_id
            ),
            entity: $outgoingLetter,
        );

        return response()->download(
            Storage::path($outgoingLetter->file_path),
            $this->fileStorage->downloadName(
                $outgoingLetter->file_path,
                $outgoingLetter->registration?->letter_number
            )
        );
    }
}
