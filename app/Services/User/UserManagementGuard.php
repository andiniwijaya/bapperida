<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Shared authorization guards for user management services.
 *
 * Business rules:
 * - Admin actors may only assign the staff role or manage staff accounts.
 * - Defense-in-depth alongside UserPolicy and Form Request validation.
 */
class UserManagementGuard
{
    public static function ensureAdminMayAssignRole(string $role): void
    {
        $actor = Auth::user();

        if ($actor?->role === 'admin' && $role !== 'staff') {
            throw ValidationException::withMessages([
                'role' => 'Admin hanya dapat membuat akun staff.',
            ]);
        }
    }

    public static function ensureAdminManagesStaffOnly(User $target): void
    {
        $actor = Auth::user();

        if ($actor?->role === 'admin' && $target->role !== 'staff') {
            throw ValidationException::withMessages([
                'user' => 'Admin hanya dapat mengelola akun staff.',
            ]);
        }
    }
}
