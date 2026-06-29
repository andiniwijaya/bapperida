<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Super Admin updates to user profile fields.
 *
 * Related modules: User (policy), Department, UpdateUserService.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Requires update permission on the route-bound user.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [

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
                Rule::unique('users')
                    ->ignore($this->route('user')),
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users')
                    ->ignore($this->route('user')),
            ],

            'department_id' => [
                'required',
                'exists:departments,id',
            ],

        ];
    }
}