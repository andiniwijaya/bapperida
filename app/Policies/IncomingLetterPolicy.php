<?php

namespace App\Policies;

use App\Models\IncomingLetter;
use App\Models\User;

/**
 * Role-based authorization for incoming letter archives.
 *
 * Business rules:
 * - All roles may view and create.
 * - Admin may edit/delete own records and staff-owned records; staff edit own only.
 * - Staff cannot delete; restore limited to own or admin/superadmin scope.
 * - forceDelete disabled globally.
 *
 * Related modules: IncomingLetter, IncomingLetterController.
 */
class IncomingLetterPolicy
{
    /**
     * Determine whether the user can view the incoming letter listing.
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
     * Determine whether the user can view an incoming letter.
     */
    public function view(User $user, IncomingLetter $incomingLetter): bool
    {
        return in_array($user->role, [
            'superadmin',
            'admin',
            'staff',
        ], true);
    }

    /**
     * Determine whether the user can create incoming letters.
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
     * Determine whether the user can update an incoming letter.
     *
     * Admin: own records or staff-created records. Staff: own records only.
     */
    public function update(User $user, IncomingLetter $incomingLetter): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin') {
            if ($incomingLetter->created_by === $user->id) {
                return true;
            }

            $owner = $incomingLetter->creator;

            return $owner && $owner->role === 'staff';
        }

        if ($user->role === 'staff') {
            return $incomingLetter->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete an incoming letter.
     *
     * Staff cannot delete incoming letters.
     */
    public function delete(User $user, IncomingLetter $incomingLetter): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin') {
            if ($incomingLetter->created_by === $user->id) {
                return true;
            }

            $owner = $incomingLetter->creator;

            return $owner && $owner->role === 'staff';
        }

        return false;
    }

    /**
     * Determine whether the user can restore a soft-deleted incoming letter.
     */
    public function restore(User $user, IncomingLetter $incomingLetter): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin') {
            if ($incomingLetter->deleted_by === $user->id || $incomingLetter->created_by === $user->id) {
                return true;
            }

            $owner = $incomingLetter->creator;

            return $owner && $owner->role === 'staff';
        }

        if ($user->role === 'staff') {
            return $incomingLetter->deleted_by === $user->id || $incomingLetter->created_by === $user->id;
        }

        return false;
    }

    /**
     * Permanent deletion is disabled for all roles.
     */
    public function forceDelete(User $user, IncomingLetter $incomingLetter): bool
    {
        return false;
    }
}
