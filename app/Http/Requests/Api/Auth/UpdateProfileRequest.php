<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates self-service profile updates for the authenticated user.
 *
 * Business rules:
 * - Email and username uniqueness ignores the current user.
 * - Avatar is optional; max 2MB image upload.
 *
 * Related modules: Auth (UpdateProfileService), User.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Only authenticated users may update their own profile.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Profile field validation rules.
     *
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'username' => [
                'required',
                'string',
                'alpha_dash',
                'min:3',
                'max:50',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'avatar' => [
                'nullable',
                'image',
                'max:2048',
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
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan underscore (_).',
            'username.unique' => 'Username sudah digunakan.',
            'avatar.image' => 'Avatar harus berupa gambar.',
            'avatar.max' => 'Avatar maksimal 2MB.',
        ];
    }
}
