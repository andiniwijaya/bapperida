<?php

namespace App\Services\Auth;

use App\Services\ActivityLog\RecordActivityLogService;
use Illuminate\Validation\ValidationException;

/**
 * Authenticates API users and issues Sanctum tokens.
 *
 * Business rules:
 * - Revokes all existing tokens before creating a new one (single active session per login).
 * - Records last login metadata (timestamp, IP, user agent).
 * - Delegates credential and status validation to AccountStatusService.
 *
 * Related modules: Auth (AccountStatusService), User (tokens, must_change_password).
 */
class LoginService
{
    public function __construct(
        protected AccountStatusService $accountStatusService,
        protected RecordActivityLogService $activityLog,
    ) {
    }

    /**
     * Validate credentials, issue a token, and update login audit fields.
     *
     * @param  array{login: string, password: string}  $credentials
     * @return array{user: \App\Models\User, token: string, must_change_password: bool}
     *
     * @throws ValidationException When login or account status checks fail.
     */
    public function handle(array $credentials): array
    {
        $user = $this->accountStatusService->authenticate(
            $credentials['login'],
            $credentials['password'],
        );

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->last_login_at = now();
        $user->last_login_ip = request()->ip();
        $user->last_login_user_agent = request()->userAgent();
        $user->save();

        $this->activityLog->record(
            action: 'login',
            module: 'auth',
            description: sprintf('Pengguna %s berhasil login.', $user->email),
            entity: $user,
            actor: $user,
        );

        return [
            'user' => $user,
            'token' => $token,
            'must_change_password' => $user->must_change_password,
        ];
    }
}
