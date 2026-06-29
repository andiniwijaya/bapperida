<?php

namespace App\Http\Controllers;

use App\Models\OutgoingLetter;
use App\Services\OutgoingLetter\ExportOutgoingLetterPdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutgoingLetterPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        return view('outgoing-letters.index');
    }

    public function create(): View
    {
        $this->authorize('create', OutgoingLetter::class);

        return view('outgoing-letters.create');
    }

    public function print(Request $request): View
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $outgoingLetters = $this->buildOutgoingLetterQuery($request)
            ->get();

        return view('pdf.outgoing-letters.print', [
            'outgoingLetters' => $outgoingLetters,
            'pdfMode' => false,
        ]);
    }

    public function exportPdf(Request $request, ExportOutgoingLetterPdfService $service)
    {
        $this->authorize('viewAny', OutgoingLetter::class);

        $outgoingLetters = $this->buildOutgoingLetterQuery($request)
            ->get();

        return $service->handle($outgoingLetters);
    }

    public function show(OutgoingLetter $outgoingLetter): View
    {
        $this->authorize('view', $outgoingLetter);

        return view('outgoing-letters.show', [
            'outgoingLetterId' => $outgoingLetter->id,
        ]);
    }

    public function edit(OutgoingLetter $outgoingLetter): View
    {
        $this->authorize('update', $outgoingLetter);

        return view('outgoing-letters.edit', [
            'outgoingLetterId' => $outgoingLetter->id,
        ]);
    }

    private function buildOutgoingLetterQuery(Request $request)
    {
        $ids = array_filter(
            array_map('intval', explode(',', $request->string('ids')->toString())),
            fn ($id) => $id > 0,
        );

        return OutgoingLetter::query()
            ->with(['registration.department'])
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->whereHas('registration', function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('letter_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('department_id') ?: null, fn ($query, $department) => $query->whereHas('registration', fn ($subQuery) => $subQuery->where('department_id', $department)))
            ->when($request->integer('year') ?: null, fn ($query, $year) => $query->whereHas('registration', fn ($subQuery) => $subQuery->where('year', $year)))
            ->when($request->string('letter_type')->trim()->toString() ?: null, fn ($query, $type) => $query->where('letter_type', $type))
            ->when($request->string('status')->trim()->toString() ?: null, fn ($query, $status) => $query->where('status', $status))
            ->latest();
    }
}
