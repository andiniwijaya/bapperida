<?php

namespace App\Http\Requests\Api\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of admin or staff users by Super Admin and staff-only by Admin.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Requires create permission on User.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedRoles = $this->user()?->role === 'admin'
            ? ['staff']
            : ['admin', 'staff'];

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
                Rule::unique('users'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users'),
            ],
            'role' => [
                'required',
                Rule::in($allowedRoles),
            ],
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid untuk akun Anda.',
            'department_id.required' => 'Bidang wajib dipilih.',
        ];
    }
}
