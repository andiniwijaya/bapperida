<?php

namespace App\Models;

use Database\Factories\IncomingLetterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Incoming letter archive (arsip surat masuk) from external institutions.
 *
 * Business rules:
 * - Does not require Letter Number Registration; letter_number is external.
 * - department_id must reference an active department; disposition department optional.
 * - Soft delete with created_by, updated_by, deleted_by audit fields.
 *
 * Related modules: Department, User, IncomingLetter services.
 */
class IncomingLetter extends Model
{
    /** @use HasFactory<IncomingLetterFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Mass-assignable archive fields. Status and audit IDs set in services.
     */
    protected $fillable = [
        'letter_number',
        'sent_date',
        'received_date',
        'disposition_date',
        'sender',
        'department_id',
        'disposition_department_id',
        'subject',
        'agenda_name',
        'summary',
        'letter_attribute',
        'attachment',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'sent_date' => 'date',
        'received_date' => 'date',
        'disposition_date' => 'date',
        'department_id' => 'integer',
        'disposition_department_id' => 'integer',
    ];

    /**
     * Primary department receiving the letter.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Department assigned for disposition handling.
     */
    public function dispositionDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'disposition_department_id');
    }

    /**
     * User who recorded this incoming letter.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated this incoming letter.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * User who soft-deleted this incoming letter.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
