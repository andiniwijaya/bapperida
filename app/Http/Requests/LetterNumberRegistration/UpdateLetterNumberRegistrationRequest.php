<?php

namespace App\Http\Requests\LetterNumberRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updates to an existing letter number registration.
 *
 * Related modules: LetterNumberRegistration (policy), UpdateLetterNumberRegistrationService.
 */
class UpdateLetterNumberRegistrationRequest extends FormRequest
{
    /**
     * Requires update permission on the route-bound registration.
     */
    public function authorize(): bool
    {
        $registration = $this->route('letterNumberRegistration') ?? $this->route('letter_number_registration');

        if (! $registration instanceof \App\Models\LetterNumberRegistration) {
            return false;
        }

        return $this->user()->can('update', $registration);
    }

    /**
     * Same rules as store; authorization differs by route model.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'index_code' => [
                'required',
                'string',
                'max:50',
            ],

            'letter_code' => [
                'required',
                'string',
                'max:50',
            ],

            'sequence_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'year' => [
                'required',
                'digits:4',
                'integer',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'summary' => [
                'nullable',
                'string',
            ],

            'recipient' => [
                'required',
                'string',
                'max:255',
            ],

            'letter_date' => [
                'required',
                'date',
            ],

            'letter_type' => [
                'required',
                Rule::in(array_keys(config('letter.types'))),
            ],

            'attachment' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],
        ];
    }

    /**
     * Human-readable attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'index_code' => 'index code',
            'letter_code' => 'letter code',
            'sequence_number' => 'sequence number',
            'year' => 'year',
            'subject' => 'subject',
            'summary' => 'summary',
            'recipient' => 'recipient',
            'letter_date' => 'letter date',
            'letter_type' => 'letter type',
            'attachment' => 'attachment',
            'notes' => 'notes',
            'department_id' => 'department',
        ];
    }
}