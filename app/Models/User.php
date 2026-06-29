<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Represents an authenticated user of the BAPPERIDA letter management system.
 *
 * Business rules:
 * - Users have roles (superadmin, admin, staff) that drive authorization across all modules.
 * - Only active users may authenticate; pending/rejected accounts are blocked at login.
 * - Soft-deleted users retain audit trail references via restrictOnDelete foreign keys.
 *
 * Related modules: Department (assignment), RegistrationRequest, Letter modules (audit fields).
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * Mass-assignable profile and credential fields.
     * Role, status, and audit fields are set explicitly in services.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'department_id',
        'avatar',
        'appearance',
    ];

    /**
     * Attributes hidden from JSON/array serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Department the user belongs to (staff registration and filtering).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Self-service registration request linked to this user account.
     */
    public function registrationRequest(): HasOne
    {
        return $this->hasOne(RegistrationRequest::class);
    }

    /**
     * Whether the user has the superadmin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Whether the user has the admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Whether the user has the staff role.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Whether the account is awaiting admin approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Whether the account is active and allowed to use the application.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Whether the registration was rejected by an administrator.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Two-letter initials derived from the user's display name (avatar fallback).
     */
    public function initials(): string
    {
        return Str::upper(Str::substr($this->name, 0, 2));
    }
}
