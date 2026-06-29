<?php

namespace App\Http\Requests\IncomingLetter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for incoming letter index listing.
 *
 * Related modules: IncomingLetterPolicy, ListIncomingLetterService.
 */
class FilterIncomingLetterRequest extends FormRequest
{
    /**
     * Requires viewAny permission on IncomingLetter model.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', \App\Models\IncomingLetter::class) ?? false;
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
            'letter_attribute' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}
