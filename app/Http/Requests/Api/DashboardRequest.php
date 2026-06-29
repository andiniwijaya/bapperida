<?php

namespace App\Http\Requests\Api;

use App\Models\Dashboard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates dashboard API filters.
 *
 * Business rules:
 * - department_id only meaningful for superadmin and admin dashboards.
 * - Staff dashboard ignores department filter server-side.
 *
 * Related modules: DashboardService, ReportStatisticsService (via filters).
 */
class DashboardRequest extends FormRequest
{
    /**
     * Requires dashboard access for the authenticated role.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return match ($user->role) {
            'superadmin' => $user->can('viewSuperAdmin', Dashboard::class),
            'admin' => $user->can('viewAdmin', Dashboard::class),
            'staff' => $user->can('viewStaff', Dashboard::class),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'granularity' => ['nullable', 'string', Rule::in(['day', 'week', 'month', 'year'])],
        ];
    }
}
