<?php

namespace App\Models;

use Database\Factories\LetterNumberRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Official letter number registration (registrasi penomoran) before outgoing archive.
 *
 * Business rules:
 * - Letter number format: {letter_code}/{sequence}/{department_code}/{year}.
 * - sequence_number is unique per year; letter_number is globally unique.
 * - Numbering cannot change once linked to an OutgoingLetter record.
 * - Soft delete preserves audit via created_by, updated_by, deleted_by.
 *
 * Related modules: Department, OutgoingLetter, User, letter config (types, status).
 */
class LetterNumberRegistration extends Model
{
    /** @use HasFactory<LetterNumberRegistrationFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Mass-assignable registration and letter metadata fields.
     * Status and audit user IDs are set in services.
     */
    protected $fillable = [
        'index_code',
        'letter_code',
        'sequence_number',
        'year',
        'letter_number',
        'subject',
        'summary',
        'recipient',
        'letter_date',
        'letter_type',
        'attachment',
        'notes',
        'department_id',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'year' => 'integer',
        'sequence_number' => 'integer',
    ];

    /**
     * Owning department for letter number suffix (department code).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * User who created this registration.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated this registration.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * User who soft-deleted this registration.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Outgoing letter archive linked to this registration (one-to-one).
     */
    public function outgoingLetter(): HasOne
    {
        return $this->hasOne(OutgoingLetter::class, 'letter_number_registration_id');
    }

    /**
     * Whether an outgoing letter (including trashed) references this registration.
     *
     * Used to block numbering changes and deletion.
     */
    public function hasOutgoingLetter(): bool
    {
        return $this->outgoingLetter()->exists();
    }
}
