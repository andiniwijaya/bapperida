<?php

namespace App\Http\Requests\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates activity log listing and export filters.
 *
 * Business rules:
 * - Only admin and superadmin may filter audit logs (authorize via ActivityLogPolicy).
 *
 * Audit trail: supports compliance filtering without exposing logs to staff.
 */
class FilterActivityLogRequest extends FormRequest
{
    /**
     * Requires viewAny permission on ActivityLog.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', \App\Models\ActivityLog::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user_role' => ['nullable', 'string', Rule::in(['superadmin', 'admin', 'staff'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}
