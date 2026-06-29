<?php



namespace App\Services\Department;



use App\Models\Department;

use App\Services\ActivityLog\RecordActivityLogService;

use App\Services\Notification\NotificationService;

use Illuminate\Support\Facades\Auth;



/**

 * Creates a new department with normalized uppercase code.

 *

 * Business rules:

 * - New departments are created with is_active true.

 *

 * Audit trail: records department creation with new department entity reference.

 *

 * Notification dispatch: departmentCreated after audit log.

 *

 * Related modules: Department, DepartmentController.

 */

class StoreDepartmentService

{

    public function __construct(
        private RecordActivityLogService $activityLog,
        private NotificationService $notificationService,
    ) {}



    /**

     * Persist a new active department.

     *

     * @param  array{code: string, name: string}  $data

     * @return Department Created department model.

     */

    public function handle(array $data): Department

    {

        $department = new Department([

            'code' => strtoupper($data['code']),

            'name' => $data['name'],

        ]);



        $department->is_active = true;

        $department->save();



        $this->activityLog->record(

            action: 'department_created',

            module: 'department',

            description: sprintf('Bidang baru %s (%s) berhasil dibuat.', $department->name, $department->code),

            entity: $department,

        );

        $this->notificationService->departmentCreated(Auth::user(), $department->name, $department->code);

        return $department;

    }

}

