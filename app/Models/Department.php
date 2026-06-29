<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organizational unit (bidang) used for user assignment and letter routing.
 *
 * Business rules:
 * - Soft-deleted departments retain referential integrity via restrictOnDelete.
 * - is_active flag controls visibility in registration and letter forms.
 * - Cannot be deleted while referenced by users or letter records (isInUse).
 *
 * Related modules: User, LetterNumberRegistration, IncomingLetter, OutgoingLetter.
 */
class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Department codes excluded from public self-registration.
     *
     * @var list<string>
     */
    public const PUBLIC_REGISTRATION_EXCLUDED_CODES = [
        'BAPPERIDA',
    ];

    /**
     * Mass-assignable department identity fields.
     */
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to active departments only.
     *
     * @param  Builder<Department>  $query
     * @return Builder<Department>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to departments selectable on public registration forms.
     *
     * @param  Builder<Department>  $query
     * @return Builder<Department>
     */
    public function scopeAvailableForPublicRegistration(Builder $query): Builder
    {
        return $query->active()
            ->whereNotIn('code', self::PUBLIC_REGISTRATION_EXCLUDED_CODES);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Staff and admin users assigned to this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Incoming letters owned by this department.
     */
    public function incomingLetters(): HasMany
    {
        return $this->hasMany(IncomingLetter::class);
    }

    /**
     * Incoming letters where this department is the disposition target.
     */
    public function dispositionIncomingLetters(): HasMany
    {
        return $this->hasMany(IncomingLetter::class, 'disposition_department_id');
    }

    /**
     * Letter number registrations filed under this department.
     */
    public function letterNumberRegistrations(): HasMany
    {
        return $this->hasMany(LetterNumberRegistration::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Whether the department is referenced by users or letter data.
     *
     * Used by DeleteDepartmentService to prevent orphaned references.
     */
    public function isInUse(): bool
    {
        if ($this->users()->exists()) {
            return true;
        }

        if ($this->incomingLetters()->exists()) {
            return true;
        }

        if ($this->dispositionIncomingLetters()->exists()) {
            return true;
        }

        if ($this->letterNumberRegistrations()->exists()) {
            return true;
        }

        return false;
    }
}
