<?php

namespace App\Http\Requests\LetterNumberRegistration;

use App\Models\LetterNumberRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates letter number preview requests before store/update.
 *
 * Related modules: PreviewLetterNumberService, LetterNumberRegistrationController.
 */
class PreviewLetterNumberRequest extends FormRequest
{
    /**
     * Requires create permission (preview is part of registration workflow).
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', LetterNumberRegistration::class) ?? false;
    }

    /**
     * Preview input validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'letter_code' => [
                'required',
                'string',
                'max:50',
            ],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],
            'sequence_number' => [
                'required',
                'integer',
                'min:1',
            ],
            'year' => [
                'nullable',
                'digits:4',
                'integer',
            ],
        ];
    }
}
