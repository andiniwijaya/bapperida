<?php

namespace App\Policies;

use App\Models\RegistrationRequest;
use App\Models\User;

class RegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any registration request.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can view the registration request.
     */
    public function view(User $user, RegistrationRequest $model): bool
    {
        return $user->role === 'superadmin' || $user->id === $model->user_id;
    }

    /**
     * Determine whether the user can approve the registration request.
     */
    public function approve(User $user, RegistrationRequest $model): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can reject the registration request.
     */
    public function reject(User $user, RegistrationRequest $model): bool
    {
        return $user->role === 'superadmin';
    }
}
