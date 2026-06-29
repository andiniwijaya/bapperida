<?php

namespace App\Http\Requests\Api\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates role change requests for admin or staff users.
 *
 * Related modules: User (changeRole policy), ChangeUserRoleService.
 */
class ChangeRoleRequest extends FormRequest
{
    /**
     * Requires changeRole permission on the route-bound user.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('changeRole', $user) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                Rule::in(['admin', 'staff']),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ];
    }
}
