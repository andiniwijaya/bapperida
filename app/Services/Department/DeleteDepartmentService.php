<?php



namespace App\Services\Department;



use App\Models\Department;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



/**

 * Soft-deletes a department after verifying it is not referenced elsewhere.

 *

 * Business rules:

 * - Sets is_active false before soft delete.

 * - Blocked when Department::isInUse() returns true.

 *

 * Audit trail: records department deletion with target department entity reference.

 *

 * Notification dispatch: departmentDeleted after audit log.

 *

 * Related modules: Department, User, letter modules.

 */

class DeleteDepartmentService

{

    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}



    /**

     * Deactivate and soft-delete the department.

     *

     * @param  Department  $department  Target department.

     *

     * @throws ValidationException When department is still in use.

     */

    public function handle(Department $department): void

    {

        if ($department->isInUse()) {

            throw ValidationException::withMessages([

                'department' => 'Bidang masih digunakan oleh pengguna atau data surat dan tidak dapat dihapus.',

            ]);

        }



        $department->is_active = false;

        $department->save();



        $department->delete();



        $this->activityLog->record(

            action: 'department_deleted',

            module: 'department',

            description: sprintf('Bidang %s (%s) berhasil dihapus.', $department->name, $department->code),

            entity: $department,

        );

        $this->notificationService->departmentDeleted(Auth::user(), $department->name, $department->code);
    }

}

