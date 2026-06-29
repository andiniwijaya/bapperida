<?php

namespace App\Http\Requests\Api\RegistrationRequest;

use App\Models\RegistrationRequest;
use Illuminate\Foundation\Http\FormRequest;

class RejectRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registrationRequest = $this->route('registrationRequest');

        return $registrationRequest instanceof RegistrationRequest
            && ($this->user()?->can('reject', $registrationRequest) ?? false);
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => [
                'required',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ];
    }
}