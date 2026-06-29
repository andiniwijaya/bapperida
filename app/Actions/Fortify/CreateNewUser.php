<?php

namespace App\Actions\Fortify;

use App\Models\Department;
use App\Services\Auth\RegisterService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Fortify action that validates and delegates web self-registration to RegisterService.
 *
 * Business rules:
 * - Mirrors API RegisterRequest validation rules for consistency.
 * - Creates pending staff accounts; does not assign admin or superadmin roles.
 *
 * Related modules: Auth (RegisterService), Department, User.
 */
class CreateNewUser implements CreatesNewUsers
{
    /**
     * @param  RegisterService  $registerService  Shared registration persistence logic.
     */
    public function __construct(
        protected RegisterService $registerService,
    ) {
    }

    /**
     * Validate input and create a pending staff user via RegisterService.
     *
     * @param  array<string, mixed>  $input  Registration form data.
     * @return \App\Models\User Newly registered user.
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'username' => [
                'required',
                'string',
                'alpha_dash',
                'min:3',
                'max:50',
                'unique:users,username',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->where(function ($query): void {
                    $query->where('is_active', true)
                        ->whereNotIn('code', Department::PUBLIC_REGISTRATION_EXCLUDED_CODES);
                }),
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],

        ])->validate();

        return $this->registerService->handle($input);
    }
}