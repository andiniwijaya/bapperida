<?php

namespace App\Http\Controllers\Api\Department;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Department\FilterDepartmentRequest;
use App\Http\Requests\Api\Department\StoreDepartmentRequest;
use App\Http\Requests\Api\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Support\ListOrder;
use App\Services\Department\DeleteDepartmentService;
use App\Services\Department\RestoreDepartmentService;
use App\Services\Department\StoreDepartmentService;
use App\Services\Department\UpdateDepartmentService;
use Illuminate\Http\JsonResponse;

/**
 * Super Admin API for department (bidang) master data.
 *
 * Business rules:
 * - CRUD restricted to superadmin via DepartmentPolicy.
 * - Soft delete deactivates department; restore validates unique code/name among active rows.
 * - List supports search and is_active filtering.
 *
 * Related modules: Department (model, services, policy), User, letter modules.
 */
class DepartmentController extends ApiController
{
    public function __construct(
        protected StoreDepartmentService $storeService,
        protected UpdateDepartmentService $updateService,
        protected DeleteDepartmentService $deleteService,
        protected RestoreDepartmentService $restoreService,
    ) {
        $this->authorizeResource(Department::class, 'department');
    }

    /**
     * Paginated, filterable list of departments.
     *
     * @param  FilterDepartmentRequest  $request  search, is_active, per_page filters.
     * @return JsonResponse DepartmentResource collection with pagination meta.
     */
    public function index(FilterDepartmentRequest $request): JsonResponse
    {
        $query = Department::query()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->has('is_active'),
                fn ($query) => $query->where('is_active', $request->boolean('is_active'))
            );

        $query = ListOrder::apply($query, $request->input('order'), 'created_at');

        $departments = $query->paginate($request->integer('per_page') ?: 15);

        return $this->success([
            'data' => DepartmentResource::collection($departments),
            'meta' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
            ],
        ], 'Data bidang berhasil diambil.');
    }

    /**
     * Show a single department record.
     *
     * @param  Department  $department  Route-model-bound department.
     * @return JsonResponse DepartmentResource.
     */
    public function show(Department $department): JsonResponse
    {
        return $this->success(
            new DepartmentResource($department),
            'Detail bidang berhasil diambil.'
        );
    }

    /**
     * Create a new active department with uppercase code.
     *
     * @param  StoreDepartmentRequest  $request  Validated code and name.
     * @return JsonResponse 201 with DepartmentResource.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->storeService->handle(
            $request->validated()
        );

        return $this->success(
            new DepartmentResource($department),
            'Bidang berhasil ditambahkan.',
            201
        );
    }

    /**
     * Update department fields including optional is_active toggle.
     *
     * @param  UpdateDepartmentRequest  $request  Validated fields.
     * @param  Department  $department  Target department.
     * @return JsonResponse Updated DepartmentResource.
     */
    public function update(
        UpdateDepartmentRequest $request,
        Department $department
    ): JsonResponse {
        $department = $this->updateService->handle(
            $department,
            $request->validated()
        );

        return $this->success(
            new DepartmentResource($department),
            'Data bidang berhasil diperbarui.'
        );
    }

    /**
     * Soft-delete department after deactivating; blocked when isInUse().
     *
     * @param  Department  $department  Target department.
     * @return JsonResponse Success message.
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->deleteService->handle($department);

        return $this->success(
            null,
            'Bidang berhasil dihapus.'
        );
    }

    /**
     * Restore a soft-deleted department by ID.
     *
     * @param  int  $id  Trashed department primary key.
     * @return JsonResponse Restored DepartmentResource.
     */
    public function restore(int $id): JsonResponse
    {
        $department = Department::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $department);

        $department = $this->restoreService->handle($department);

        return $this->success(
            new DepartmentResource($department),
            'Bidang berhasil dipulihkan.'
        );
    }
}
