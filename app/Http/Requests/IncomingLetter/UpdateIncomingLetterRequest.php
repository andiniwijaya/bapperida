<?php

namespace App\Http\Requests\IncomingLetter;

use App\Models\IncomingLetter;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates incoming letter update payload and optional PDF replacement.
 *
 * Related modules: IncomingLetterPolicy, UpdateIncomingLetterService.
 */
class UpdateIncomingLetterRequest extends FormRequest
{
    /**
     * Requires update permission on route-bound incoming letter.
     */
    public function authorize(): bool
    {
        $incomingLetter = $this->route('incomingLetter') ?? $this->route('incoming_letter');

        if (! $incomingLetter instanceof IncomingLetter) {
            return false;
        }

        return $this->user()->can('update', $incomingLetter);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $incomingLetter = $this->route('incomingLetter') ?? $this->route('incoming_letter');

        return [
            'letter_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('incoming_letters', 'letter_number')
                    ->ignore($incomingLetter?->id)
                    ->whereNull('deleted_at'),
            ],
            'sent_date' => ['required', 'date'],
            'received_date' => ['required', 'date'],
            'disposition_date' => ['nullable', 'date'],
            'sender' => ['required', 'string', 'max:255'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],
            'disposition_department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],
            'subject' => ['required', 'string', 'max:255'],
            'agenda_name' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'letter_attribute' => ['required', Rule::in(array_keys(config('letter.types')))],
            'attachment' => ['nullable', 'string', 'max:255'],
            'file' => app(SystemConfigurationService::class)->uploadFileRules(required: false),
            'status' => ['required', Rule::in(array_keys(config('status.incoming_letter')))],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'letter_number' => 'nomor surat',
            'sent_date' => 'tanggal surat',
            'received_date' => 'tanggal diterima',
            'disposition_date' => 'tanggal disposisi',
            'sender' => 'pengirim',
            'department_id' => 'bidang',
            'disposition_department_id' => 'bidang disposisi',
            'subject' => 'perihal',
            'agenda_name' => 'nama agenda',
            'summary' => 'isi ringkas',
            'letter_attribute' => 'jenis surat',
            'attachment' => 'lampiran',
            'file' => 'file PDF',
            'status' => 'status',
            'notes' => 'catatan',
        ];
    }
}
