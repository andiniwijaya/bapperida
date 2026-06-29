<?php

namespace App\Policies;

use App\Models\LetterNumberRegistration;
use App\Models\User;

/**
 * Role-based authorization for letter number registrations.
 *
 * Business rules:
 * - All roles may view and create.
 * - Admin may edit/delete own records and staff-owned records; staff edit own only.
 * - Staff cannot delete; restore limited to own or admin/superadmin scope.
 * - forceDelete disabled globally.
 *
 * Related modules: LetterNumberRegistration, User, OutgoingLetter.
 */
class LetterNumberRegistrationPolicy
{
    /**
     * Determine whether the user can view the registration listing.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ]);
    }

    /**
     * Determine whether the user can view a registration.
     */
    public function view(User $user, LetterNumberRegistration $registration): bool
    {
        // All roles can view
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ]);
    }

    /**
     * Determine whether the user can create registrations.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ]);
    }

    /**
     * Determine whether the user can update a registration.
     *
     * Admin: own records or staff-created records. Staff: own records only.
     */
    public function update(User $user, LetterNumberRegistration $registration): bool
    {
        // Super Admin
        if ($user->role === 'superadmin') {
            return true;
        }

        // Admin
        if ($user->role === 'admin') {

            // Boleh edit milik sendiri
            if ($registration->created_by === $user->id) {
                return true;
            }

            // Cari pemilik data
            $owner = $registration->creator;

            // Boleh edit milik Staff
            return $owner && $owner->role === 'staff';
        }

        // Staff
        if ($user->role === 'staff') {
            return $registration->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a registration.
     *
     * Staff cannot delete. Superadmin and admin per ownership rules.
     */
    public function delete(User $user, LetterNumberRegistration $registration): bool
    {
        // Super Admin
        if ($user->role === 'superadmin') {
            return true;
        }

        // Admin
        if ($user->role === 'admin') {

            // Hapus milik sendiri
            if ($registration->created_by === $user->id) {
                return true;
            }

            // Hapus milik Staff
            $owner = $registration->creator;

            return $owner && $owner->role === 'staff';
        }

        // Staff tidak boleh hapus
        return false;
    }

    /**
     * Determine whether the user can restore a soft-deleted registration.
     */
    public function restore(User $user, LetterNumberRegistration $registration): bool
    {
        // Super Admin can restore anything
        if ($user->role === 'superadmin') {
            return true;
        }

        // Admin can restore their own or staff's data
        if ($user->role === 'admin') {
            if ($registration->deleted_by === $user->id || $registration->created_by === $user->id) {
                return true;
            }

            $owner = $registration->creator;
            return $owner && $owner->role === 'staff';
        }

        // Staff can restore their own data
        if ($user->role === 'staff') {
            return $registration->deleted_by === $user->id || $registration->created_by === $user->id;
        }

        return false;
    }

    /**
     * Permanent deletion is disabled for all roles.
     */
    public function forceDelete(User $user, LetterNumberRegistration $registration): bool
    {
        return false;
    }
}