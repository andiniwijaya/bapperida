<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\LetterNumberRegistration\FilterLetterNumberRegistrationRequest;
use App\Http\Requests\LetterNumberRegistration\PreviewLetterNumberRequest;
use App\Http\Requests\LetterNumberRegistration\StoreLetterNumberRegistrationRequest;
use App\Http\Requests\LetterNumberRegistration\UpdateLetterNumberRegistrationRequest;
use App\Http\Resources\LetterNumberRegistrationResource;
use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Services\LetterNumberRegistration\AvailableSequenceService;
use App\Services\LetterNumberRegistration\DeleteLetterNumberRegistrationService;
use App\Services\LetterNumberRegistration\ListLetterNumberRegistrationService;
use App\Services\LetterNumberRegistration\PreviewLetterNumberService;
use App\Services\LetterNumberRegistration\StoreLetterNumberRegistrationService;
use App\Services\LetterNumberRegistration\UpdateLetterNumberRegistrationService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API for letter number registration (registrasi penomoran surat).
 *
 * Business rules:
 * - All authenticated roles may view/create; update/delete follow role-based policy.
 * - Preview and available-sequences assist form UX before store.
 * - create/filters endpoints supply metadata for frontend forms.
 *
 * Related modules: LetterNumberRegistration (model, services, policy), Department, OutgoingLetter.
 */
class LetterNumberRegistrationController extends ApiController
{
    /**
     * Paginated, filterable list of registrations.
     *
     * @param  FilterLetterNumberRegistrationRequest  $request  Query filters.
     * @param  ListLetterNumberRegistrationService  $service  Query builder service.
     * @return JsonResponse Resource collection with pagination meta.
     */
    public function index(FilterLetterNumberRegistrationRequest $request, ListLetterNumberRegistrationService $service): JsonResponse
    {
        $this->authorize('viewAny', LetterNumberRegistration::class);

        $registrations = $service->handle([
            'search' => $request->string('search')->trim()->toString(),
            'year' => $request->integer('year') ?: null,
            'department_id' => $request->integer('department_id') ?: null,
            'letter_type' => $request->string('letter_type')->trim()->toString() ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'per_page' => $request->integer('per_page', 10),
            'order' => $request->input('order'),
        ]);

        return $this->success([
            'data' => LetterNumberRegistrationResource::collection($registrations),
            'meta' => [
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
            ],
        ], 'Registrations retrieved successfully.');
    }

    /**
     * Create a registration with sequence locking and letter number preview.
     *
     * @param  StoreLetterNumberRegistrationRequest  $request  Validated registration payload.
     * @param  StoreLetterNumberRegistrationService  $service  Persistence with concurrency guard.
     * @return JsonResponse 201 with LetterNumberRegistrationResource.
     */
    public function store(
        StoreLetterNumberRegistrationRequest $request,
        StoreLetterNumberRegistrationService $service
    ): JsonResponse {

        $registration = $service->handle(
            $request->validated()
        );

        return $this->success(
            new LetterNumberRegistrationResource(
                $registration->load([
                    'department',
                    'creator',
                ])
            ),
            'Registration created successfully.',
            201
        );
    }

    /**
     * Show a single registration with department and creator.
     *
     * @param  LetterNumberRegistration  $letterNumberRegistration  Route-model-bound record.
     * @return JsonResponse LetterNumberRegistrationResource.
     */
    public function show(
        LetterNumberRegistration $letterNumberRegistration
    ) {
        $this->authorize(
            'view',
            $letterNumberRegistration
        );

        return $this->success(
            new LetterNumberRegistrationResource(
                $letterNumberRegistration->load([
                    'department',
                    'creator',
                ])
            ),
            'Registration retrieved successfully.'
        );
    }

    /**
     * Update registration; numbering locked when outgoing letter exists.
     *
     * @param  UpdateLetterNumberRegistrationRequest  $request  Validated fields.
     * @param  LetterNumberRegistration  $letterNumberRegistration  Target record.
     * @param  UpdateLetterNumberRegistrationService  $service  Update with sequence guard.
     * @return JsonResponse Updated LetterNumberRegistrationResource.
     */
    public function update(
        UpdateLetterNumberRegistrationRequest $request,
        LetterNumberRegistration $letterNumberRegistration,
        UpdateLetterNumberRegistrationService $service
    ): JsonResponse {
        $this->authorize('update', $letterNumberRegistration);

        $registration = $service->handle(
            $letterNumberRegistration,
            $request->validated()
        );

        return $this->success(
            new LetterNumberRegistrationResource(
                $registration->load([
                    'department',
                    'creator',
                ])
            ),
            'Registration updated successfully.'
        );
    }

    /**
     * Soft-delete registration; blocked when linked to outgoing letter.
     *
     * @param  LetterNumberRegistration  $letterNumberRegistration  Target record.
     * @param  DeleteLetterNumberRegistrationService  $service  Delete with business rules.
     * @return JsonResponse Success message.
     */
    public function destroy(
        LetterNumberRegistration $letterNumberRegistration,
        DeleteLetterNumberRegistrationService $service
    ): JsonResponse {

        $this->authorize('delete', $letterNumberRegistration);

        $service->handle(
            $letterNumberRegistration
        );

        return $this->success(
            null,
            'Registration deleted successfully.'
        );
    }

    /**
     * Restore a soft-deleted registration after sequence uniqueness checks.
     *
     * @param  int  $id  Trashed registration primary key.
     * @param  \App\Services\LetterNumberRegistration\RestoreLetterNumberRegistrationService  $service
     * @return JsonResponse Restored LetterNumberRegistrationResource.
     */
    public function restore(
        int $id,
        \App\Services\LetterNumberRegistration\RestoreLetterNumberRegistrationService $service
    ): JsonResponse {
        $registration = LetterNumberRegistration::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $registration);

        $registration = $service->handle($registration);

        return $this->success(
            new LetterNumberRegistrationResource(
                $registration->load(['department', 'creator'])
            ),
            'Registration restored successfully.'
        );
    }

    /**
     * Preview computed letter number without persisting.
     *
     * @param  PreviewLetterNumberRequest  $request  letter_code, department_id, optional sequence/year.
     * @param  PreviewLetterNumberService  $service  Number formatting logic.
     * @return JsonResponse sequence_number and letter_number preview.
     */
    public function preview(
        PreviewLetterNumberRequest $request,
        PreviewLetterNumberService $service
    ): JsonResponse {
        $this->authorize('create', LetterNumberRegistration::class);

        return $this->success(
            $service->handle(
                letterCode: $request->string('letter_code')->toString(),
                departmentId: $request->integer('department_id'),
                sequenceNumber: $request->integer('sequence_number') ?: null,
                year: $request->integer('year') ?: null,
            ),
            'Preview generated successfully.'
        );
    }

    /**
     * List unused sequence numbers for a given year (form helper).
     *
     * @param  Request  $request  Optional year query parameter.
     * @param  AvailableSequenceService  $service  Gap detection in sequence usage.
     * @return JsonResponse Array of available sequence integers.
     */
    public function availableSequences(
        Request $request,
        AvailableSequenceService $service
    ): JsonResponse {
        $this->authorize('create', LetterNumberRegistration::class);

        return $this->success(
            $service->handle(
                $request->integer('year') ?: null
            ),
            'Available sequences retrieved successfully.'
        );
    }

    /**
     * Distinct filter options for list UI (years, departments, types, statuses).
     *
     * @return JsonResponse Filter metadata for frontend dropdowns.
     */
    public function filters(): JsonResponse
    {
        $this->authorize('viewAny', LetterNumberRegistration::class);

        $years = LetterNumberRegistration::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $departments = Department::query()
            ->active()
            ->select([
                'id',
                'code',
                'name',
            ])
            ->orderBy('name')
            ->get();

        $letterTypes = collect(config('letter.types'))
            ->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })
            ->values();

        $statuses = collect(config('status.letter_registration'))
            ->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })
            ->values();

        return $this->success([
            'years' => $years,
            'departments' => $departments,
            'letter_types' => $letterTypes,
            'statuses' => $statuses,
        ], 'Letter registration filters retrieved successfully.');
    }

    /**
     * Metadata for the create form (departments, types, available sequences).
     *
     * @param  Request  $request  Optional year override.
     * @param  AvailableSequenceService  $availableSequenceService  Sequence gap helper.
     * @return JsonResponse Form initialization payload.
     */
    public function create(
        Request $request,
        AvailableSequenceService $availableSequenceService,
        SystemConfigurationService $configuration,
    ): JsonResponse {
        $this->authorize('create', LetterNumberRegistration::class);

        $departments = Department::query()
            ->active()
            ->select([
                'id',
                'code',
                'name',
            ])
            ->orderBy('name')
            ->get();

        $letterTypes = collect(config('letter.types'))
            ->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values();

        $year = $request->integer('year') ?: $configuration->activeYear();

        return $this->success([
            'current_year' => $year,
            'active_year' => $configuration->activeYear(),
            'letter_number_template' => $configuration->letterNumberTemplate(),
            'letter_prefix' => $configuration->letterPrefix(),
            'letter_start_number' => $configuration->letterStartNumber(),
            'default_letter_type' => $configuration->defaultLetterType(),
            'default_letter_priority' => $configuration->defaultLetterPriority(),
            'departments' => $departments,
            'letter_types' => $letterTypes,
            'available_sequences' => $availableSequenceService->handle($year),
        ], 'Letter registration create metadata retrieved successfully.');
    }
}
