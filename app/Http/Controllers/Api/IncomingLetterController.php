<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\IncomingLetter\FilterIncomingLetterRequest;
use App\Http\Requests\IncomingLetter\StoreIncomingLetterRequest;
use App\Http\Requests\IncomingLetter\UpdateIncomingLetterRequest;
use App\Http\Resources\IncomingLetterResource;
use App\Models\Department;
use App\Models\IncomingLetter;
use App\Services\IncomingLetter\DeleteIncomingLetterService;
use App\Services\IncomingLetter\DownloadIncomingLetterFileService;
use App\Services\IncomingLetter\ExportIncomingLetterExcelService;
use App\Services\IncomingLetter\ExportIncomingLetterPdfService;
use App\Services\IncomingLetter\ListIncomingLetterService;
use App\Services\IncomingLetter\RestoreIncomingLetterService;
use App\Services\IncomingLetter\StoreIncomingLetterService;
use App\Services\IncomingLetter\UpdateIncomingLetterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomingLetterController extends ApiController
{
    public function index(FilterIncomingLetterRequest $request, ListIncomingLetterService $service): JsonResponse
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $incomingLetters = $service->handle([
            'search' => $request->string('search')->trim()->toString() ?: null,
            'year' => $request->integer('year') ?: null,
            'department_id' => $request->integer('department_id') ?: null,
            'letter_attribute' => $request->string('letter_attribute')->trim()->toString() ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'per_page' => $request->integer('per_page', 10),
            'order' => $request->input('order'),
        ]);

        return $this->success([
            'data' => IncomingLetterResource::collection($incomingLetters),
            'meta' => [
                'current_page' => $incomingLetters->currentPage(),
                'last_page' => $incomingLetters->lastPage(),
                'per_page' => $incomingLetters->perPage(),
                'total' => $incomingLetters->total(),
            ],
        ], 'Incoming letters retrieved successfully.');
    }

    public function filters(): JsonResponse
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $years = IncomingLetter::query()
            ->selectRaw($this->yearSelectExpression('received_date'))
            ->orderByDesc('year')
            ->pluck('year');

        $departments = Department::query()
            ->active()
            ->select(['id', 'code', 'name'])
            ->orderBy('name')
            ->get();

        $letterAttributes = collect(config('letter.types'))
            ->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values();

        $statuses = collect(config('status.incoming_letter'))
            ->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values();

        return $this->success([
            'years' => $years,
            'departments' => $departments,
            'letter_attributes' => $letterAttributes,
            'statuses' => $statuses,
        ], 'Incoming letter filters retrieved successfully.');
    }

    private function yearSelectExpression(string $column): string
    {
        $expression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y', {$column})"
            : "year({$column})";

        return "distinct {$expression} as year";
    }

    public function store(StoreIncomingLetterRequest $request, StoreIncomingLetterService $service): JsonResponse
    {
        $incomingLetter = $service->handle(
            $request->validated(),
            $request->file('file'),
        );

        return $this->success(
            new IncomingLetterResource($incomingLetter->load(['department', 'dispositionDepartment', 'creator'])),
            'Arsip surat masuk berhasil dibuat.',
            201
        );
    }

    public function show(IncomingLetter $incomingLetter): JsonResponse
    {
        $this->authorize('view', $incomingLetter);

        return $this->success(
            new IncomingLetterResource($incomingLetter->load(['department', 'dispositionDepartment', 'creator'])),
            'Detail arsip surat masuk berhasil diambil.'
        );
    }

    public function update(
        UpdateIncomingLetterRequest $request,
        IncomingLetter $incomingLetter,
        UpdateIncomingLetterService $service
    ): JsonResponse {
        $this->authorize('update', $incomingLetter);

        $incomingLetter = $service->handle(
            $incomingLetter,
            $request->validated(),
            $request->file('file'),
        );

        return $this->success(
            new IncomingLetterResource($incomingLetter->load(['department', 'dispositionDepartment', 'creator'])),
            'Arsip surat masuk berhasil diperbarui.'
        );
    }

    public function destroy(IncomingLetter $incomingLetter, DeleteIncomingLetterService $service): JsonResponse
    {
        $this->authorize('delete', $incomingLetter);

        $service->handle($incomingLetter);

        return $this->success(null, 'Arsip surat masuk berhasil dihapus.');
    }

    public function restore(
        int $id,
        RestoreIncomingLetterService $service
    ): JsonResponse {
        $incomingLetter = IncomingLetter::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $incomingLetter);

        $incomingLetter = $service->handle($incomingLetter);

        return $this->success(
            new IncomingLetterResource($incomingLetter->load(['department', 'dispositionDepartment', 'creator'])),
            'Arsip surat masuk berhasil dipulihkan.'
        );
    }

    public function downloadFile(IncomingLetter $incomingLetter, DownloadIncomingLetterFileService $service)
    {
        $this->authorize('view', $incomingLetter);

        return $service->handle($incomingLetter);
    }

    public function print(Request $request)
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $ids = array_filter(array_map('intval', explode(',', $request->string('ids')->toString())), fn ($id) => $id > 0);

        $query = IncomingLetter::query()->with(['department', 'dispositionDepartment']);

        if ($ids) {
            $query->whereIn('id', $ids);
        } else {
            $query->when($request->string('search')->trim()->toString(), function ($q, $search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('sender', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('agenda_name', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            })
                ->when($request->integer('year') ?: null, fn ($q, $year) => $q->whereYear('received_date', $year))
                ->when($request->integer('department_id') ?: null, fn ($q, $department) => $q->where('department_id', $department))
                ->when($request->string('letter_attribute')->trim()->toString() ?: null, fn ($q, $attribute) => $q->where('letter_attribute', $attribute))
                ->when($request->string('status')->trim()->toString() ?: null, fn ($q, $status) => $q->where('status', $status));
        }

        return view('pdf.incoming-letters.print', [
            'incomingLetters' => $query->get(),
            'pdfMode' => false,
        ]);
    }

    public function exportPdf(Request $request, ExportIncomingLetterPdfService $service)
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $ids = array_filter(array_map('intval', explode(',', $request->string('ids')->toString())), fn ($id) => $id > 0);

        $query = IncomingLetter::query()->with(['department', 'dispositionDepartment']);

        if ($ids) {
            $query->whereIn('id', $ids);
        } else {
            $query->when($request->string('search')->trim()->toString(), function ($q, $search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('sender', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('agenda_name', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            })
                ->when($request->integer('year') ?: null, fn ($q, $year) => $q->whereYear('received_date', $year))
                ->when($request->integer('department_id') ?: null, fn ($q, $department) => $q->where('department_id', $department))
                ->when($request->string('letter_attribute')->trim()->toString() ?: null, fn ($q, $attribute) => $q->where('letter_attribute', $attribute))
                ->when($request->string('status')->trim()->toString() ?: null, fn ($q, $status) => $q->where('status', $status));
        }

        return $service->handle($query->get());
    }

    public function exportExcel(Request $request, ExportIncomingLetterExcelService $service)
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $ids = array_filter(array_map('intval', explode(',', $request->string('ids')->toString())), fn ($id) => $id > 0);

        $query = IncomingLetter::query()->with(['department', 'dispositionDepartment', 'creator']);

        if ($ids) {
            $query->whereIn('id', $ids);
        } else {
            $query->when($request->string('search')->trim()->toString(), function ($q, $search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('sender', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('agenda_name', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            })
                ->when($request->integer('year') ?: null, fn ($q, $year) => $q->whereYear('received_date', $year))
                ->when($request->integer('department_id') ?: null, fn ($q, $department) => $q->where('department_id', $department))
                ->when($request->string('letter_attribute')->trim()->toString() ?: null, fn ($q, $attribute) => $q->where('letter_attribute', $attribute))
                ->when($request->string('status')->trim()->toString() ?: null, fn ($q, $status) => $q->where('status', $status));
        }

        return $service->handle($query->get());
    }
}
