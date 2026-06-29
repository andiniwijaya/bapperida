<?php

namespace App\Http\Requests\Api\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of a new department.
 *
 * Business rules:
 * - Code normalized to uppercase in prepareForValidation.
 * - Uniqueness scoped to non-deleted rows.
 *
 * Related modules: Department (policy), StoreDepartmentService.
 */
class StoreDepartmentRequest extends FormRequest
{
    /**
     * Requires create permission on Department (superadmin).
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Department::class) ?? false;
    }

    /**
     * Normalize department code to uppercase before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'alpha_dash',
                'max:20',
                Rule::unique('departments', 'code')->whereNull('deleted_at'),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'name')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode bidang wajib diisi.',
            'code.alpha_dash' => 'Kode bidang hanya boleh berisi huruf, angka, strip, dan underscore.',
            'code.unique' => 'Kode bidang sudah digunakan.',

            'name.required' => 'Nama bidang wajib diisi.',
            'name.unique' => 'Nama bidang sudah digunakan.',
        ];
    }
}
