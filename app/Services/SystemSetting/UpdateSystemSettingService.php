<?php

namespace App\Services\SystemSetting;

use App\Models\SystemSetting;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;

/**
 * Updates application system settings and invalidates configuration cache.
 *
 * Business rules:
 * - Only superadmin may update via API policy layer.
 * - Cache invalidation ensures modules read fresh values immediately.
 *
 * Audit trail: records setting update with changed field keys in properties.
 * Notification dispatch: systemSettingUpdated after audit log.
 */
class UpdateSystemSettingService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private GetSystemSettingService $getService,
        private NotificationService $notificationService,
    ) {}

    /**
     * Apply validated setting changes, invalidate cache, and record audit trail.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(SystemSetting $systemSetting, array $data): SystemSetting
    {
        $changedKeys = array_keys($data);

        $systemSetting->update($data);

        $this->getService->forgetCache();

        $systemSetting = $systemSetting->fresh();

        $this->activityLog->record(
            action: 'setting_updated',
            module: 'system_setting',
            description: sprintf(
                'Pengaturan sistem diperbarui (%d field: %s).',
                count($changedKeys),
                implode(', ', $changedKeys)
            ),
            entity: $systemSetting,
            properties: [
                'changed_keys' => $changedKeys,
            ],
        );

        $this->notificationService->systemSettingUpdated(Auth::user());

        return $systemSetting;
    }
}
