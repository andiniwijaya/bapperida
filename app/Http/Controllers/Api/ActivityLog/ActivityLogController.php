<?php

namespace App\Http\Controllers\Api\ActivityLog;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ActivityLog\FilterActivityLogRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\ActivityLog\ExportActivityLogExcelService;
use App\Services\ActivityLog\ListActivityLogService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Http\JsonResponse;

/**
 * Read-only API for enterprise audit trail.
 *
 * Business rules:
 * - Listing and export only; logs are created via RecordActivityLogService.
 * - Authorization via ActivityLogPolicy (admin and superadmin).
 *
 * Audit trail: official query endpoint for compliance investigations.
 */
class ActivityLogController extends ApiController
{
    public function __construct(
        protected ListActivityLogService $listService,
        protected ExportActivityLogExcelService $exportService,
        protected SystemConfigurationService $configuration,
    ) {}

    /**
     * Paginated audit log listing with filters.
     */
    public function index(FilterActivityLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ActivityLog::class);

        $activityLogs = $this->listService->handle($this->filtersFromRequest($request));

        return $this->success([
            'data' => ActivityLogResource::collection($activityLogs),
            'meta' => [
                'current_page' => $activityLogs->currentPage(),
                'last_page' => $activityLogs->lastPage(),
                'per_page' => $activityLogs->perPage(),
                'total' => $activityLogs->total(),
            ],
        ], 'Activity logs retrieved successfully.');
    }

    /**
     * Single audit log detail.
     */
    public function show(ActivityLog $activityLog): JsonResponse
    {
        $this->authorize('view', $activityLog);

        return $this->success(
            new ActivityLogResource($activityLog->load(['user', 'department'])),
            'Activity log retrieved successfully.'
        );
    }

    /**
     * Export filtered audit logs to Excel.
     */
    public function exportExcel(FilterActivityLogRequest $request)
    {
        $this->authorize('export', ActivityLog::class);

        $filters = array_merge($this->filtersFromRequest($request), [
            'per_page' => $this->configuration->activityLogMaxExport(),
        ]);

        $logs = $this->listService->handle($filters);

        return $this->exportService->handle($logs);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(FilterActivityLogRequest $request): array
    {
        return [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'module' => $request->string('module')->trim()->toString() ?: null,
            'action' => $request->string('action')->trim()->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'user_role' => $request->string('user_role')->trim()->toString() ?: null,
            'department_id' => $request->integer('department_id') ?: null,
            'period_start' => $request->string('period_start')->trim()->toString() ?: null,
            'period_end' => $request->string('period_end')->trim()->toString() ?: null,
            'per_page' => $request->integer('per_page', 15),
            'order' => $request->input('order'),
        ];
    }
}
