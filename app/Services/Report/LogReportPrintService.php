<?php

namespace App\Services\Report;

use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;

/**
 * Records audit trail entries for report print views.
 *
 * Audit trail: separates print logging from presentation controllers.
 * Notification dispatch: reportPrinted after audit log.
 */
class LogReportPrintService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function record(array $filters): void
    {
        $this->activityLog->record(
            action: 'print',
            module: 'report',
            description: sprintf(
                'Pengguna mencetak laporan tipe %s.',
                $filters['report_type'] ?? 'all'
            ),
            actor: Auth::user(),
            properties: $filters,
        );

        $this->notificationService->reportPrinted(Auth::user());
    }
}
