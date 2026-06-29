<?php

namespace App\Http\Requests\Api\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updates to an existing department.
 *
 * Related modules: Department (policy), UpdateDepartmentService.
 */
class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Requires update permission on the route-bound department.
     */
    public function authorize(): bool
    {
        $department = $this->route('department');

        return $department instanceof Department
            && ($this->user()?->can('update', $department) ?? false);
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
        /** @var Department $department */
        $department = $this->route('department');

        return [
            'code' => [
                'required',
                'string',
                'alpha_dash',
                'max:20',
                Rule::unique('departments', 'code')
                    ->ignore($department->id)
                    ->whereNull('deleted_at'),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'name')
                    ->ignore($department->id)
                    ->whereNull('deleted_at'),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
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
