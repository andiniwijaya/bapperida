<?php

namespace App\Http\Requests\OutgoingLetter;

use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of an outgoing letter archive with PDF upload.
 *
 * Business rules:
 * - letter_number_registration_id must be active, unused, and unique on outgoing_letters.
 * - PDF required, max 10MB.
 *
 * Related modules: OutgoingLetter (policy), StoreOutgoingLetterService, LetterNumberRegistration.
 */
class StoreOutgoingLetterRequest extends FormRequest
{
    /**
     * Requires create permission on OutgoingLetter.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\OutgoingLetter::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'letter_number_registration_id' => [
                'required',
                'integer',
                Rule::exists('letter_number_registrations', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', 'active'),
                Rule::unique('outgoing_letters', 'letter_number_registration_id'),
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
            'file' => app(SystemConfigurationService::class)->uploadFileRules(required: true),
            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'letter_number_registration_id' => 'registrasi penomoran',
            'letter_type' => 'jenis surat',
            'attachment' => 'lampiran',
            'file' => 'file PDF',
            'notes' => 'catatan',
        ];
    }
}
