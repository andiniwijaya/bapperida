<?php

namespace App\Services\ActivityLog;

use App\Models\ActivityLog;
use App\Support\ListOrder;

/**
 * Paginated activity log listing with audit filters.
 *
 * Business rules:
 * - Eager loads user and department to avoid N+1 on list API.
 * - Supports compliance filters: user, role, department, module, action, date range.
 *
 * Related modules: ActivityLogController, FilterActivityLogRequest.
 */
class ListActivityLogService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function handle(array $filters = [])
    {
        $query = ActivityLog::query()
            ->with(['user', 'department'])
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->where(function ($query) use ($search) {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('entity_type', 'like', "%{$search}%");
                })
            )
            ->when(
                $filters['module'] ?? null,
                fn ($query, $module) => $query->where('module', $module)
            )
            ->when(
                $filters['action'] ?? null,
                fn ($query, $action) => $query->where('action', $action)
            )
            ->when(
                $filters['user_id'] ?? null,
                fn ($query, $userId) => $query->where('user_id', $userId)
            )
            ->when(
                $filters['user_role'] ?? null,
                fn ($query, $role) => $query->where('user_role', $role)
            )
            ->when(
                $filters['department_id'] ?? null,
                fn ($query, $departmentId) => $query->where('department_id', $departmentId)
            )
            ->when(
                $filters['period_start'] ?? null,
                fn ($query, $start) => $query->whereDate('logged_at', '>=', $start)
            )
            ->when(
                $filters['period_end'] ?? null,
                fn ($query, $end) => $query->whereDate('logged_at', '<=', $end)
            );

        $query = ListOrder::apply($query, $filters['order'] ?? null, 'logged_at');

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
