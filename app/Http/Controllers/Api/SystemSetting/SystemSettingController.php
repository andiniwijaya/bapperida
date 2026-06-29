<?php

namespace App\Http\Controllers\Api\SystemSetting;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\SystemSetting\UpdateSystemSettingRequest;
use App\Http\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use App\Services\SystemSetting\GetSystemSettingService;
use App\Services\SystemSetting\UpdateSystemSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Read/update API for singleton application configuration.
 *
 * Business rules:
 * - View and update: superadmin only (SystemSettingPolicy).
 * - All reads go through GetSystemSettingService (cached).
 *
 * Configuration impact: changes propagate after cache invalidation in update service.
 */
class SystemSettingController extends ApiController
{
    public function __construct(
        protected GetSystemSettingService $getService,
        protected UpdateSystemSettingService $updateService,
    ) {}

    /**
     * Return current system configuration grouped by category.
     */
    public function show(): JsonResponse
    {
        $systemSetting = $this->getService->handle();

        $this->authorize('view', $systemSetting);

        return $this->success(
            new SystemSettingResource($systemSetting),
            'System setting retrieved successfully.'
        );
    }

    /**
     * Apply validated configuration changes (superadmin only).
     */
    public function update(UpdateSystemSettingRequest $request): JsonResponse
    {
        $systemSetting = $this->getService->handle();

        $this->authorize('update', $systemSetting);

        $systemSetting = $this->updateService->handle(
            $systemSetting,
            $request->validated(),
        );

        return $this->success(
            new SystemSettingResource($systemSetting),
            'System setting updated successfully.'
        );
    }
}
