<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates authenticated password change requests.
 *
 * Business rules:
 * - Requires an active session (authorize checks auth()->check()).
 * - New password must be confirmed and meet Password::defaults().
 *
 * Related modules: Auth (ChangePasswordService, AuthController).
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Only authenticated users may change their password.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules for current and new password fields.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ];
    }
}
