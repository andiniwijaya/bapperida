<?php

namespace App\Http\Requests\Api\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for paginated department listing.
 *
 * Related modules: Department (viewAny policy), DepartmentController.
 */
class FilterDepartmentRequest extends FormRequest
{
    /**
     * Requires viewAny permission on Department (superadmin).
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Department::class) ?? false;
    }

    /**
     * Filter and pagination rules for the index endpoint.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}
