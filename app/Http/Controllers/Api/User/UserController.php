<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\User\ChangeRoleRequest;
use App\Http\Requests\Api\User\ChangeStatusRequest;
use App\Http\Requests\Api\User\ResendPasswordSetupRequest;
use App\Http\Requests\Api\User\ResetPasswordRequest;
use App\Http\Requests\Api\User\StoreUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Requests\Api\User\FilterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ListOrder;
use App\Services\User\ChangeUserRoleService;
use App\Services\User\ChangeUserStatusService;
use App\Services\User\DeleteUserService;
use App\Services\User\ResendPasswordSetupEmailService;
use App\Services\User\ResetUserPasswordService;
use App\Services\User\RestoreUserService;
use App\Services\User\StoreUserService;
use App\Services\User\UpdateUserService;
use Illuminate\Http\JsonResponse;

/**
 * API for user lifecycle management by Super Admin and scoped Admin staff management.
 *
 * Business rules:
 * - Super Admin: full user lifecycle via UserPolicy.
 * - Admin: staff-only create, view, update, delete, restore, and reset password.
 * - Role and status changes remain Super Admin only.
 */
class UserController extends ApiController
{
    public function __construct(
        protected StoreUserService $storeService,
        protected UpdateUserService $updateService,
        protected DeleteUserService $deleteService,
        protected RestoreUserService $restoreService,
        protected ChangeUserRoleService $changeRoleService,
        protected ChangeUserStatusService $changeStatusService,
        protected ResetUserPasswordService $resetPasswordService,
        protected ResendPasswordSetupEmailService $resendPasswordSetupService,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Paginated list of users with department relation.
     *
     * @return JsonResponse UserResource collection (10 per page).
     */
    public function index(FilterUserRequest $request): JsonResponse
    {
        $query = User::query()
            ->with('department')
            ->when(
                $request->user()?->role === 'admin',
                fn ($query) => $query->where('role', 'staff'),
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
            );

        $query = ListOrder::apply($query, $request->input('order'), 'created_at');

        $paginated = $query->paginate($request->integer('per_page', 15));

        return $this->success([
            'data' => UserResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ], 'Data pengguna berhasil diambil.');
    }

    /**
     * Show a single user with department details.
     *
     * @param  User  $user  Route-model-bound user.
     * @return JsonResponse UserResource with department loaded.
     */
    public function show(User $user): JsonResponse
    {
        return $this->success(
            new UserResource($user->load('department')),
            'Detail pengguna berhasil diambil.'
        );
    }

    /**
     * Create an admin or staff user and send a password-setup email.
     *
     * @param  StoreUserRequest  $request  Validated user fields.
     * @return JsonResponse 201 with UserResource.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $result = $this->storeService->handle(
            $request->validated()
        );

        return $this->success(
            new UserResource($result['user']->load('department')),
            'Pengguna berhasil ditambahkan.',
            201
        );
    }

    /**
     * Update profile fields for an existing user (not role or status).
     *
     * @param  UpdateUserRequest  $request  Validated fields.
     * @param  User  $user  Target user.
     * @return JsonResponse Updated UserResource.
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {
        $user = $this->updateService->handle(
            $user,
            $request->validated()
        );

        return $this->success(
            new UserResource($user->load('department')),
            'Data pengguna berhasil diperbarui.'
        );
    }

    /**
     * Change a user's role (admin or staff only; superadmin protected in service).
     *
     * @param  ChangeRoleRequest  $request  Must include role field.
     * @param  User  $user  Target user.
     * @return JsonResponse Updated UserResource.
     */
    public function changeRole(
        ChangeRoleRequest $request,
        User $user
    ): JsonResponse {
        $user = $this->changeRoleService->handle(
            $user,
            $request->validated('role')
        );

        return $this->success(
            new UserResource($user->load('department')),
            'Role pengguna berhasil diperbarui.'
        );
    }

    /**
     * Change account status (pending, active, rejected); superadmin accounts are protected.
     *
     * @param  ChangeStatusRequest  $request  Must include status field.
     * @param  User  $user  Target user.
     * @return JsonResponse Updated UserResource.
     */
    public function changeStatus(
        ChangeStatusRequest $request,
        User $user
    ): JsonResponse {
        $user = $this->changeStatusService->handle(
            $user,
            $request->validated('status')
        );

        return $this->success(
            new UserResource($user->load('department')),
            'Status pengguna berhasil diperbarui.'
        );
    }

    /**
     * Initiate password reset via Fortify email link (no temporary password).
     *
     * @param  ResetPasswordRequest  $request  Authorization only; no body fields.
     * @param  User  $user  Target user.
     * @return JsonResponse Updated UserResource.
     */
    public function resetPassword(
        ResetPasswordRequest $request,
        User $user
    ): JsonResponse {
        $result = $this->resetPasswordService->handle($user);

        return $this->success(
            new UserResource($result['user']->load('department')),
            'Email atur kata sandi berhasil dikirim.'
        );
    }

    /**
     * Resend password-setup email for users who have not completed onboarding.
     *
     * @param  ResendPasswordSetupRequest  $request  Authorization only.
     * @param  User  $user  Target user.
     * @return JsonResponse Updated UserResource.
     */
    public function resendPasswordSetup(
        ResendPasswordSetupRequest $request,
        User $user
    ): JsonResponse {
        $result = $this->resendPasswordSetupService->handle($user);

        return $this->success(
            new UserResource($result['user']->load('department')),
            'Email atur kata sandi berhasil dikirim ulang.'
        );
    }

    /**
     * Soft-delete a user; self-delete and superadmin deletion blocked in service.
     *
     * @param  User  $user  Target user.
     * @return JsonResponse Success message without body data.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->deleteService->handle($user);

        return $this->success(
            null,
            'Pengguna berhasil dihapus.'
        );
    }

    /**
     * Restore a soft-deleted user by primary key.
     *
     * @param  int  $id  Trashed user ID.
     * @return JsonResponse Restored UserResource.
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $user);

        $user = $this->restoreService->handle($user);

        return $this->success(
            new UserResource($user->load('department')),
            'Pengguna berhasil dipulihkan.'
        );
    }
}
