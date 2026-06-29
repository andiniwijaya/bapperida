<?php

namespace App\Services\Department;

use App\Models\Department;
use App\Services\ActivityLog\RecordActivityLogService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Restores a soft-deleted department after uniqueness checks.
 *
 * Business rules:
 * - Code and name must not conflict with another active department.
 *
 * Audit trail: records department restoration with target department entity reference.
 *
 * Notification dispatch: departmentRestored after audit log.
 *
 * Related modules: Department, DepartmentController.
 */
class RestoreDepartmentService
{
    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}

    /**
     * Restore trashed department when code/name are unique among active rows.
     *
     * @param  Department  $department  Trashed department model.
     * @return Department Refreshed active department.
     *
     * @throws ValidationException When not trashed or uniqueness conflict exists.
     */
    public function handle(Department $department): Department
    {
        if (! $department->trashed()) {
            throw ValidationException::withMessages([
                'department' => 'Bidang tidak dalam status terhapus.',
            ]);
        }

        $this->assertUniqueAmongActive($department);

        $department->restore();

        $department = $department->fresh();

        $this->activityLog->record(
            action: 'department_restored',
            module: 'department',
            description: sprintf('Bidang %s (%s) berhasil dipulihkan.', $department->name, $department->code),
            entity: $department,
        );

        $this->notificationService->departmentRestored(Auth::user(), $department->name, $department->code);

        return $department;
    }

    /**
     * Ensure restored department code and name do not collide with active records.
     *
     * @param  Department  $department  Department being restored.
     *
     * @throws ValidationException When code or name already exists on another row.
     */
    private function assertUniqueAmongActive(Department $department): void
    {
        $codeConflict = Department::query()
            ->where('code', $department->code)
            ->whereKeyNot($department->id)
            ->exists();

        if ($codeConflict) {
            throw ValidationException::withMessages([
                'code' => 'Kode bidang sudah digunakan oleh bidang aktif lain.',
            ]);
        }

        $nameConflict = Department::query()
            ->where('name', $department->name)
            ->whereKeyNot($department->id)
            ->exists();

        if ($nameConflict) {
            throw ValidationException::withMessages([
                'name' => 'Nama bidang sudah digunakan oleh bidang aktif lain.',
            ]);
        }
    }
}
