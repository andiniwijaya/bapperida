<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Issues Fortify password-reset links for admin-driven account onboarding.
 *
 * Business rules:
 * - Assigns a strong random password hash that is never exposed to administrators.
 * - Creates a Laravel password broker token for the Fortify reset-password form.
 * - Tokens are single-use and managed by the existing password_reset_tokens table.
 */
class IssuePasswordSetupLinkService
{
    /**
     * Replace the user's password with a strong random hash and require password setup.
     */
    public function assignUnusablePassword(User $user): void
    {
        $randomPassword = Str::password(
            length: 64,
            letters: true,
            numbers: true,
            symbols: true,
            spaces: false,
        );

        $user->password = Hash::make($randomPassword);
        $user->must_change_password = true;
        $user->save();
    }

    /**
     * Create a Fortify-compatible password reset URL for the given user.
     */
    public function createResetUrl(User $user): string
    {
        $broker = Password::broker(config('fortify.passwords'));
        $token = $broker->createToken($user);

        return route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}
