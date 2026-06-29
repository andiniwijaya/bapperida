<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Services\Auth\RegisterService;
use App\Services\Auth\LoginService;
use App\Services\Auth\ChangePasswordService;
use App\Services\Auth\LogoutService;
use App\Services\Auth\UpdateProfileService;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * API entry point for authentication and self-service profile management.
 *
 * Business rules:
 * - Staff self-registration creates pending accounts awaiting Super Admin approval.
 * - Login issues Sanctum tokens and enforces account status via AccountStatusService.
 * - Profile and password changes require an authenticated, active session.
 *
 * Related modules: User (UserResource), Auth services, Fortify (web registration).
 */
class AuthController extends ApiController
{
    public function __construct(
        protected RegisterService $registerService,
        protected LoginService $loginService,
        protected ChangePasswordService $changePasswordService,
        protected LogoutService $logoutService,
        protected UpdateProfileService $updateProfileService,
    ) {
    }

    /**
     * Register a new staff account pending Super Admin approval.
     *
     * @param  RegisterRequest  $request  Validated registration payload.
     * @return JsonResponse 201 with basic user fields; no token is issued.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerService->handle(
            $request->validated()
        );

        return $this->success(
            [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'status' => $user->status,
            ],
            'Registrasi berhasil. Menunggu persetujuan Super Admin.',
            201
        );
    }

    /**
     * Authenticate by email/username and password; issue a Sanctum bearer token.
     *
     * @param  LoginRequest  $request  Login credentials.
     * @return JsonResponse Token, user resource, and must_change_password flag.
     *
     * @throws \Illuminate\Validation\ValidationException When credentials or account status are invalid.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginService->handle(
            $request->validated()
        );

        return $this->success(
        [
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
            'must_change_password' => $result['must_change_password'],
            'user' => new UserResource($result['user']),
        ],
        'Login berhasil.'
        );
    }

    /**
     * Change the authenticated user's password after verifying the current one.
     *
     * @param  ChangePasswordRequest  $request  Current and new password fields.
     * @return JsonResponse Success message; clears must_change_password when applicable.
     */
    public function changePassword(
        ChangePasswordRequest $request
    ): JsonResponse {

        $this->changePasswordService->handle(
            Auth::user(),
            $request->validated()
        );

        return $this->success(
            null,
            'Password berhasil diubah.'
        );
    }

    /**
     * Revoke the current Sanctum access token for the authenticated user.
     *
     * @return JsonResponse Success message after token deletion.
     */
    public function logout(): JsonResponse
    {
        $this->logoutService->handle(Auth::user());

        return $this->success(
            null,
            'Logout berhasil.'
        );
    }

    /**
     * Return the currently authenticated user as a UserResource.
     *
     * @return JsonResponse Authenticated user profile with department and permissions.
     */
    public function me(): JsonResponse
    {
        return $this->success(
            new UserResource(Auth::user()),
            'Data pengguna berhasil diambil.'
        );
    }

    /**
     * Update the authenticated user's profile (name, email, username, avatar).
     *
     * @param  UpdateProfileRequest  $request  Validated profile fields.
     * @return JsonResponse Updated user resource; email change resets verification.
     */
    public function updateProfile(
        UpdateProfileRequest $request
    ): JsonResponse {
        $user = $this->updateProfileService->handle(
            Auth::user(),
            $request->validated()
        );

        return $this->success(
            new UserResource($user),
            'Profil berhasil diperbarui.'
        );
    }
}
