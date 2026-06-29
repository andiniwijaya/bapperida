<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates API login credentials (email/username + password).
 *
 * Related modules: Auth (AuthController::login, LoginService).
 */
class LoginRequest extends FormRequest
{
    /**
     * Login is a public endpoint; no prior authentication required.
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

            'login' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
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

            'login.required' => 'Username atau email wajib diisi.',

            'password.required' => 'Password wajib diisi.',

        ];
    }
}