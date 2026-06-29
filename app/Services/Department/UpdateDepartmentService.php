<?php



namespace App\Services\Department;



use App\Models\Department;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Support\Facades\Auth;



/**

 * Updates department identity and active status.

 *

 * Audit trail: records department update with target department entity reference.

 *

 * Notification dispatch: departmentUpdated after audit log.

 *

 * Related modules: Department, DepartmentController.

 */

class UpdateDepartmentService

{

    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}



    /**

     * Apply updates with uppercase code normalization.

     *

     * @param  array{code: string, name: string, is_active?: bool}  $data

     * @return Department Refreshed department model.

     */

    public function handle(

        Department $department,

        array $data

    ): Department {

        $department->code = strtoupper($data['code']);

        $department->name = $data['name'];



        if (array_key_exists('is_active', $data)) {

            $department->is_active = (bool) $data['is_active'];

        }



        $department->save();



        $department = $department->fresh();



        $this->activityLog->record(

            action: 'department_updated',

            module: 'department',

            description: sprintf('Data bidang %s (%s) berhasil diperbarui.', $department->name, $department->code),

            entity: $department,

        );

        $this->notificationService->departmentUpdated(Auth::user(), $department->name, $department->code);

        return $department;

    }

}

