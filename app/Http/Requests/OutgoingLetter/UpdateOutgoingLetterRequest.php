<?php

namespace App\Http\Requests\OutgoingLetter;

use App\Models\OutgoingLetter;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updates to an outgoing letter archive.
 *
 * Related modules: OutgoingLetter (policy), UpdateOutgoingLetterService.
 */
class UpdateOutgoingLetterRequest extends FormRequest
{
    /**
     * Requires update permission on the route-bound outgoing letter.
     */
    public function authorize(): bool
    {
        $outgoingLetter = $this->route('outgoingLetter') ?? $this->route('outgoing_letter');

        if (! $outgoingLetter instanceof OutgoingLetter) {
            return false;
        }

        return $this->user()->can('update', $outgoingLetter);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'letter_type' => [
                'required',
                Rule::in(array_keys(config('letter.types'))),
            ],
            'attachment' => [
                'nullable',
                'string',
                'max:255',
            ],
            'file' => app(SystemConfigurationService::class)->uploadFileRules(required: false),
            'notes' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in(array_keys(config('status.outgoing_letter'))),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'letter_type' => 'jenis surat',
            'attachment' => 'lampiran',
            'file' => 'file PDF',
            'notes' => 'catatan',
            'status' => 'status',
        ];
    }
}
