<?php

namespace App\Services\OutgoingLetter;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Handles PDF storage, deletion, and download naming for outgoing letters.
 *
 * Business rules:
 * - Only PDF files accepted; stored under public/outgoing-letters with unique names.
 * - Download filename sanitized from official letter number when available.
 *
 * Related modules: OutgoingLetter, Store/Update services, OutgoingLetterController.
 */
class OutgoingLetterFileStorage
{
    /**
     * Validate PDF and store with timestamped unique filename.
     *
     * @param  UploadedFile  $file  Uploaded PDF from request.
     * @return string Storage path relative to disk root.
     *
     * @throws ValidationException When file is not PDF.
     */
    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');

        if ($extension !== 'pdf') {
            throw ValidationException::withMessages([
                'file' => 'File harus berformat PDF.',
            ]);
        }

        $fileName = sprintf(
            '%s_%s.pdf',
            now()->format('YmdHis'),
            uniqid('', true),
        );

        return $file->storeAs('public/outgoing-letters', $fileName);
    }

    /**
     * Remove file from storage when path exists.
     *
     * @param  string|null  $path  Stored file path or null.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    /**
     * Build a safe download filename from letter number or stored path.
     *
     * @param  string|null  $filePath  Stored path for fallback basename.
     * @param  string|null  $letterNumber  Official number for preferred filename.
     * @return string Filename ending in .pdf.
     */
    public function downloadName(?string $filePath, ?string $letterNumber = null): string
    {
        if ($letterNumber) {
            $safeNumber = preg_replace('/[^A-Za-z0-9._-]+/', '_', $letterNumber);

            return $safeNumber.'.pdf';
        }

        if ($filePath) {
            return pathinfo($filePath, PATHINFO_BASENAME);
        }

        return 'arsip-surat-keluar.pdf';
    }
}
