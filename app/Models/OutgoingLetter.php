<?php

namespace App\Models;

use Database\Factories\OutgoingLetterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Outgoing letter archive (arsip surat keluar) linked one-to-one to a registration.
 *
 * Business rules:
 * - Each active registration may have at most one outgoing letter (including trashed check on create).
 * - Stores PDF file path and letter metadata; registration supplies official number.
 * - Soft delete with created_by, updated_by, deleted_by audit fields.
 *
 * Related modules: LetterNumberRegistration, User, OutgoingLetter services.
 */
class OutgoingLetter extends Model
{
    /** @use HasFactory<OutgoingLetterFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Mass-assignable archive fields. Status and audit IDs set in services.
     */
    protected $fillable = [
        'letter_number_registration_id',
        'letter_type',
        'attachment',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'letter_number_registration_id' => 'integer',
    ];

    /**
     * Parent letter number registration supplying official number and metadata.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(LetterNumberRegistration::class, 'letter_number_registration_id');
    }

    /**
     * User who created this outgoing letter archive.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated this outgoing letter archive.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * User who soft-deleted this outgoing letter archive.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
