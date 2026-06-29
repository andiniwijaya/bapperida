<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\OutgoingLetter\FilterOutgoingLetterRequest;
use App\Http\Requests\OutgoingLetter\StoreOutgoingLetterRequest;
use App\Http\Requests\OutgoingLetter\UpdateOutgoingLetterRequest;
use App\Http\Resources\OutgoingLetterResource;
use App\Models\Department;
use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use App\Services\OutgoingLetter\DeleteOutgoingLetterService;
use App\Services\OutgoingLetter\DownloadOutgoingLetterFileService;
use App\Services\OutgoingLetter\ExportOutgoingLetterExcelService;
use App\Services\OutgoingLetter\ExportOutgoingLetterPdfService;
use App\Services\OutgoingLetter\ListOutgoingLetterService;
use App\Services\OutgoingLetter\RestoreOutgoingLetterService;
use App\Services\OutgoingLetter\StoreOutgoingLetterService;
use App\Services\OutgoingLetter\UpdateOutgoingLetterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API for outgoing letter archives (arsip surat keluar).
 *
 * Business rules:
 * - Created from active registrations without existing outgoing letter.
 * - PDF file required on create; optional replace on update.
 * - Supports print preview, PDF/Excel export, and secure file download.
 * - Update/delete authorization follows role-based OutgoingLetterPolicy.
 *
 * Related modules: OutgoingLetter (model, services, policy), LetterNumberRegistration.
 */
class OutgoingLetterController extends ApiController
{
    /**
     * Paginated, filterable list of outgoing letters.
     *
     * @param  FilterOutgoingLetterRequest  $request  Query filters.
     * @param  ListOutgoingLetterService  $service  Query builder.
     * @return JsonResponse Resource collection with pagination meta.
     */
    public function index(FilterOutgoingLetterRequest $request, ListOutgoingLetterService $service): JsonResponse
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $outgoingLetters = $service->handle([
            'search' => $request->string('search')->trim()->toString() ?: null,
            'year' => $request->integer('year') ?: null,
            'department_id' => $request->integer('department_id') ?: null,
            'letter_type' => $request->string('letter_type')->trim()->toString() ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'per_page' => $request->integer('per_page', 10),
            'order' => $request->input('order'),
        ]);

        return $this->success([
            'data' => OutgoingLetterResource::collection($outgoingLetters),
            'meta' => [
                'current_page' => $outgoingLetters->currentPage(),
                'last_page' => $outgoingLetters->lastPage(),
                'per_page' => $outgoingLetters->perPage(),
                'total' => $outgoingLetters->total(),
            ],
        ], 'Outgoing letters retrieved successfully.');
    }

    /**
     * Metadata for create form: eligible registrations and letter types.
     *
     * @param  Request  $request  Unused; reserved for future filters.
     * @return JsonResponse Registrations without outgoing letter, types, statuses.
     */
    public function create(Request $request): JsonResponse
    {
        $this->authorize('create', OutgoingLetter::class);

        $registrations = LetterNumberRegistration::query()
            ->where('status', 'active')
            ->whereDoesntHave('outgoingLetter', function ($query) {
                $query->withTrashed();
            })
            ->with('department')
            ->orderByDesc('year')
            ->orderByDesc('sequence_number')
            ->get();

        $letterTypes = collect(config('letter.types'))
            ->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values();

        return $this->success([
            'registrations' => $registrations,
            'letter_types' => $letterTypes,
            'statuses' => collect(config('status.outgoing_letter'))
                ->map(fn ($label, $value) => [
                    'value' => $value,
                    'label' => $label,
                ])->values(),
        ], 'Outgoing letter create metadata retrieved successfully.');
    }

    /**
     * Distinct filter options for list UI.
     *
     * @return JsonResponse Years, departments, letter types, statuses.
     */
    public function filters(): JsonResponse
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $years = OutgoingLetter::query()
            ->join('letter_number_registrations', 'outgoing_letters.letter_number_registration_id', '=', 'letter_number_registrations.id')
            ->select('letter_number_registrations.year')
            ->distinct()
            ->orderByDesc('letter_number_registrations.year')
            ->pluck('year');

        $departments = Department::query()
            ->active()
            ->select(['id', 'code', 'name'])
            ->orderBy('name')
            ->get();

        $letterTypes = collect(config('letter.types'))
            ->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values();

        $statuses = collect(config('status.outgoing_letter'))
            ->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values();

        return $this->success([
            'years' => $years,
            'departments' => $departments,
            'letter_types' => $letterTypes,
            'statuses' => $statuses,
        ], 'Outgoing letter filters retrieved successfully.');
    }

    /**
     * Create outgoing letter archive with PDF upload.
     *
     * @param  StoreOutgoingLetterRequest  $request  Validated fields and file.
     * @param  StoreOutgoingLetterService  $service  Transactional create with registration lock.
     * @return JsonResponse 201 with OutgoingLetterResource.
     */
    public function store(StoreOutgoingLetterRequest $request, StoreOutgoingLetterService $service): JsonResponse
    {
        $outgoingLetter = $service->handle(
            $request->validated(),
            $request->file('file'),
        );

        return $this->success(
            new OutgoingLetterResource($outgoingLetter->load(['registration.department', 'creator'])),
            'Arsip surat keluar berhasil dibuat.',
            201
        );
    }

    /**
     * Show single outgoing letter with registration and department.
     *
     * @param  OutgoingLetter  $outgoingLetter  Route-model-bound record.
     * @return JsonResponse OutgoingLetterResource.
     */
    public function show(OutgoingLetter $outgoingLetter): JsonResponse
    {
        $this->authorize('view', $outgoingLetter);

        return $this->success(
            new OutgoingLetterResource($outgoingLetter->load(['registration.department', 'creator'])),
            'Detail arsip surat keluar berhasil diambil.'
        );
    }

    /**
     * Update metadata, status, and optionally replace PDF file.
     *
     * @param  UpdateOutgoingLetterRequest  $request  Validated update payload.
     * @param  OutgoingLetter  $outgoingLetter  Target record.
     * @param  UpdateOutgoingLetterService  $service  Update with file handling.
     * @return JsonResponse Updated OutgoingLetterResource.
     */
    public function update(
        UpdateOutgoingLetterRequest $request,
        OutgoingLetter $outgoingLetter,
        UpdateOutgoingLetterService $service
    ): JsonResponse {
        $this->authorize('update', $outgoingLetter);

        $outgoingLetter = $service->handle(
            $outgoingLetter,
            $request->validated(),
            $request->file('file'),
        );

        return $this->success(
            new OutgoingLetterResource($outgoingLetter->load(['registration.department', 'creator'])),
            'Arsip surat keluar berhasil diperbarui.'
        );
    }

    /**
     * Soft-delete outgoing letter archive.
     *
     * @param  OutgoingLetter  $outgoingLetter  Target record.
     * @param  DeleteOutgoingLetterService  $service  Records deleter and deletes.
     * @return JsonResponse Success message.
     */
    public function destroy(OutgoingLetter $outgoingLetter, DeleteOutgoingLetterService $service): JsonResponse
    {
        $this->authorize('delete', $outgoingLetter);

        $service->handle($outgoingLetter);

        return $this->success(null, 'Arsip surat keluar berhasil dihapus.');
    }

    /**
     * Restore soft-deleted outgoing letter when registration is not conflicted.
     *
     * @param  int  $id  Trashed outgoing letter primary key.
     * @param  RestoreOutgoingLetterService  $service  Restore with uniqueness check.
     * @return JsonResponse Restored OutgoingLetterResource.
     */
    public function restore(
        int $id,
        RestoreOutgoingLetterService $service
    ): JsonResponse {
        $outgoingLetter = OutgoingLetter::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $outgoingLetter);

        $outgoingLetter = $service->handle($outgoingLetter);

        return $this->success(
            new OutgoingLetterResource($outgoingLetter->load(['registration.department', 'creator'])),
            'Arsip surat keluar berhasil dipulihkan.'
        );
    }

    /**
     * Download stored PDF file with letter-number-based filename.
     *
     * Audit trail: file download is logged in DownloadOutgoingLetterFileService.
     *
     * @param  OutgoingLetter  $outgoingLetter  Target record.
     * @param  DownloadOutgoingLetterFileService  $service  Secure download with audit trail.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadFile(OutgoingLetter $outgoingLetter, DownloadOutgoingLetterFileService $service)
    {
        $this->authorize('view', $outgoingLetter);

        $outgoingLetter->loadMissing('registration');

        return $service->handle($outgoingLetter);
    }

    /**
     * HTML print preview for selected or filtered outgoing letters.
     *
     * @param  Request  $request  ids comma-list or same filters as index.
     * @return \Illuminate\View\View
     */
    public function print(Request $request)
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $ids = array_filter(array_map('intval', explode(',', $request->string('ids')->toString())), fn ($id) => $id > 0);

        $query = OutgoingLetter::query()->with(['registration.department']);

        if ($ids) {
            $query->whereIn('id', $ids);
        } else {
            $query->when($request->string('search')->trim()->toString(), function ($q, $search) {
                $q->whereHas('registration', function ($query) use ($search) {
                    $query->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('letter_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('year') ?: null, fn ($q, $year) => $q->whereHas('registration', fn ($query) => $query->where('year', $year)))
            ->when($request->integer('department_id') ?: null, fn ($q, $department) => $q->whereHas('registration', fn ($query) => $query->where('department_id', $department)))
            ->when($request->string('letter_type')->trim()->toString() ?: null, fn ($q, $type) => $q->where('letter_type', $type))
            ->when($request->string('status')->trim()->toString() ?: null, fn ($q, $status) => $q->where('status', $status));
        }

        $outgoingLetters = $query->get();

        return view('pdf.outgoing-letters.print', [
            'outgoingLetters' => $outgoingLetters,
            'pdfMode' => false,
        ]);
    }

    /**
     * Export filtered or selected records to PDF download.
     *
     * @param  Request  $request  ids or filter query params.
     * @param  ExportOutgoingLetterPdfService  $service  PDF renderer.
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request, ExportOutgoingLetterPdfService $service)
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $ids = array_filter(array_map('intval', explode(',', $request->string('ids')->toString())), fn ($id) => $id > 0);

        $query = OutgoingLetter::query()->with(['registration.department']);

        if ($ids) {
            $query->whereIn('id', $ids);
        } else {
            $query->when($request->string('search')->trim()->toString(), function ($q, $search) {
                $q->whereHas('registration', function ($query) use ($search) {
                    $query->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('letter_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('year') ?: null, fn ($q, $year) => $q->whereHas('registration', fn ($query) => $query->where('year', $year)))
            ->when($request->integer('department_id') ?: null, fn ($q, $department) => $q->whereHas('registration', fn ($query) => $query->where('department_id', $department)))
            ->when($request->string('letter_type')->trim()->toString() ?: null, fn ($q, $type) => $q->where('letter_type', $type))
            ->when($request->string('status')->trim()->toString() ?: null, fn ($q, $status) => $q->where('status', $status));
        }

        return $service->handle($query->get());
    }

    /**
     * Export filtered or selected records to Excel download.
     *
     * @param  Request  $request  ids or filter query params.
     * @param  ExportOutgoingLetterExcelService  $service  Spreadsheet builder.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request, ExportOutgoingLetterExcelService $service)
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $ids = array_filter(array_map('intval', explode(',', $request->string('ids')->toString())), fn ($id) => $id > 0);

        $query = OutgoingLetter::query()->with(['registration.department', 'creator']);

        if ($ids) {
            $query->whereIn('id', $ids);
        } else {
            $query->when($request->string('search')->trim()->toString(), function ($q, $search) {
                $q->whereHas('registration', function ($query) use ($search) {
                    $query->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('letter_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('year') ?: null, fn ($q, $year) => $q->whereHas('registration', fn ($query) => $query->where('year', $year)))
            ->when($request->integer('department_id') ?: null, fn ($q, $department) => $q->whereHas('registration', fn ($query) => $query->where('department_id', $department)))
            ->when($request->string('letter_type')->trim()->toString() ?: null, fn ($q, $type) => $q->where('letter_type', $type))
            ->when($request->string('status')->trim()->toString() ?: null, fn ($q, $status) => $q->where('status', $status));
        }

        return $service->handle($query->get());
    }
}
