<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Centralizes login and route-access checks based on user account status.
 *
 * Business rules:
 * - Login requires verified email plus active status (pending/rejected blocked).
 * - Protected routes skip email verification but still block pending/rejected/inactive.
 * - Login identifier accepts email or username.
 *
 * Related modules: User, Auth (LoginService), middleware (EnsureAccountIsActive, RoleMiddleware).
 */
class AccountStatusService
{
    /**
     * Throw a validation error when the user may not log in.
     *
     * @param  User  $user  Candidate user after password verification.
     *
     * @throws ValidationException When loginDeniedMessage returns a message.
     */
    public function validateForLogin(User $user): void
    {
        $message = $this->loginDeniedMessage($user);

        if ($message !== null) {
            throw ValidationException::withMessages([
                'login' => $message,
            ]);
        }
    }

    /**
     * Resolve the login rejection message, or null when login is allowed.
     *
     * @param  User  $user  User attempting to authenticate.
     * @return string|null Localized denial reason, or null if login may proceed.
     */
    public function loginDeniedMessage(User $user): ?string
    {
        if (! $user->hasVerifiedEmail()) {
            return 'Silakan verifikasi email terlebih dahulu.';
        }

        if ($user->isPending()) {
            return 'Akun Anda masih menunggu persetujuan Super Admin.';
        }

        if ($user->isRejected()) {
            return 'Registrasi akun Anda ditolak.';
        }

        if (! $user->isActive()) {
            return 'Akun Anda tidak aktif.';
        }

        return null;
    }

    /**
     * Resolve the protected-route rejection message, or null when access is allowed.
     *
     * Does not require email verification (unlike loginDeniedMessage).
     *
     * @param  User  $user  Authenticated user.
     * @return string|null Localized denial reason, or null if access may proceed.
     */
    public function accessDeniedMessage(User $user): ?string
    {
        if ($user->isPending()) {
            return 'Akun Anda masih menunggu persetujuan Super Admin.';
        }

        if ($user->isRejected()) {
            return 'Registrasi akun Anda ditolak.';
        }

        if (! $user->isActive()) {
            return 'Akun Anda tidak aktif.';
        }

        return null;
    }

    /**
     * Find a user by email or username (case-sensitive match on stored values).
     *
     * @param  string  $login  Email address or username from the login form.
     * @return User|null Matching user, or null when not found.
     */
    public function findByLogin(string $login): ?User
    {
        return User::query()
            ->where(function ($query) use ($login): void {
                $query->where('email', $login)
                    ->orWhere('username', $login);
            })
            ->first();
    }

    /**
     * Verify credentials and return the user when login is permitted.
     *
     * @param  string  $login  Email or username.
     * @param  string  $password  Plain-text password.
     * @return User Authenticated user passing all status checks.
     *
     * @throws ValidationException When credentials are wrong or account status blocks login.
     */
    public function authenticate(string $login, string $password): User
    {
        $user = $this->findByLogin($login);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Email/Username atau password salah.',
            ]);
        }

        $this->validateForLogin($user);

        return $user;
    }
}
