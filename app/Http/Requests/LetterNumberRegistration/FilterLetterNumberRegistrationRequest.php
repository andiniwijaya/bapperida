<?php

namespace App\Http\Requests\LetterNumberRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query filters for letter number registration listing.
 *
 * Related modules: LetterNumberRegistration (viewAny policy), ListLetterNumberRegistrationService.
 */
class FilterLetterNumberRegistrationRequest extends FormRequest
{
    /**
     * Requires viewAny permission on LetterNumberRegistration.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', \App\Models\LetterNumberRegistration::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'letter_type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}
