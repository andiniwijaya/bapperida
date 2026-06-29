<?php

namespace App\Policies;

use App\Models\OutgoingLetter;
use App\Models\User;

/**
 * Role-based authorization for outgoing letter archives.
 *
 * Business rules:
 * - All roles may view and create.
 * - Admin may edit/delete own and staff records; staff edit own only; staff cannot delete.
 * - Restore rules mirror LetterNumberRegistrationPolicy ownership model.
 * - forceDelete disabled globally.
 *
 * Related modules: OutgoingLetter, User, LetterNumberRegistration.
 */
class OutgoingLetterPolicy
{
    /**
     * Determine whether the user can view the outgoing letter listing.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ], true);
    }

    /**
     * Determine whether the user can view an outgoing letter.
     */
    public function view(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ], true);
    }

    /**
     * Determine whether the user can create outgoing letters.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ], true);
    }

    /**
     * Determine whether the user can update an outgoing letter.
     */
    public function update(User $user, OutgoingLetter $outgoingLetter): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin') {
            if ($outgoingLetter->created_by === $user->id) {
                return true;
            }

            $owner = $outgoingLetter->creator;

            return $owner && $owner->role === 'staff';
        }

        if ($user->role === 'staff') {
            return $outgoingLetter->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete an outgoing letter.
     *
     * Staff cannot delete outgoing letters.
     */
    public function delete(User $user, OutgoingLetter $outgoingLetter): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin') {
            if ($outgoingLetter->created_by === $user->id) {
                return true;
            }

            $owner = $outgoingLetter->creator;

            return $owner && $owner->role === 'staff';
        }

        return false;
    }

    /**
     * Determine whether the user can restore a soft-deleted outgoing letter.
     */
    public function restore(User $user, OutgoingLetter $outgoingLetter): bool
    {
        // Super Admin can restore anything
        if ($user->role === 'superadmin') {
            return true;
        }

        // Admin can restore their own or staff's data
        if ($user->role === 'admin') {
            if ($outgoingLetter->deleted_by === $user->id || $outgoingLetter->created_by === $user->id) {
                return true;
            }

            $owner = $outgoingLetter->creator;
            return $owner && $owner->role === 'staff';
        }

        // Staff can restore their own data
        if ($user->role === 'staff') {
            return $outgoingLetter->deleted_by === $user->id || $outgoingLetter->created_by === $user->id;
        }

        return false;
    }

    /**
     * Permanent deletion is disabled for all roles.
     */
    public function forceDelete(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return false;
    }
}
