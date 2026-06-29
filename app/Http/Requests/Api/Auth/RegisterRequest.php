<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates staff self-registration via the API.
 *
 * Business rules:
 * - department_id must reference an active department.
 * - Password must meet application defaults and confirmation.
 *
 * Related modules: Auth (RegisterService), Department, User.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Registration is a public endpoint; no prior authentication required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
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

            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan underscore (_).',
            'username.unique' => 'Username sudah digunakan.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',

            'department_id.required' => 'Bidang wajib dipilih.',
            'department_id.exists' => 'Bidang yang dipilih tidak valid.',

            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',

        ];
    }
}