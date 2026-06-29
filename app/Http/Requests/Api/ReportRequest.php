<?php

namespace App\Http\Requests\Api;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates report listing and export filters.
 *
 * Business rules:
 * - department_id must reference an active, non-deleted department when provided.
 * - letter_type maps to classification fields per module in ListReportService.
 *
 * Related modules: ListReportService, ReportController, ReportPageController.
 */
class ReportRequest extends FormRequest
{
    /**
     * Requires viewAny permission on Report marker model.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Report::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'report_type' => ['nullable', 'string', 'in:all,registration,incoming,outgoing'],
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
            'year' => ['nullable', 'integer', 'digits:4'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'letter_type' => ['nullable', 'string', Rule::in(array_keys(config('letter.types')))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'ids' => ['nullable', 'string'],
        ];
    }
}
