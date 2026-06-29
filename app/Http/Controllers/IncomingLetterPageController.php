<?php

namespace App\Http\Controllers;

use App\Models\IncomingLetter;
use App\Services\IncomingLetter\ExportIncomingLetterPdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomingLetterPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', IncomingLetter::class);

        return view('incoming-letters.index');
    }

    public function create(): View
    {
        $this->authorize('create', IncomingLetter::class);

        return view('incoming-letters.create');
    }

    public function print(Request $request): View
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $incomingLetters = $this->buildIncomingLetterQuery($request)
            ->get();

        return view('pdf.incoming-letters.print', [
            'incomingLetters' => $incomingLetters,
            'pdfMode' => false,
        ]);
    }

    public function exportPdf(Request $request, ExportIncomingLetterPdfService $service)
    {
        $this->authorize('viewAny', IncomingLetter::class);

        $incomingLetters = $this->buildIncomingLetterQuery($request)
            ->get();

        return $service->handle($incomingLetters);
    }

    public function show(IncomingLetter $incomingLetter): View
    {
        $this->authorize('view', $incomingLetter);

        return view('incoming-letters.show', [
            'incomingLetterId' => $incomingLetter->id,
        ]);
    }

    public function edit(IncomingLetter $incomingLetter): View
    {
        $this->authorize('update', $incomingLetter);

        return view('incoming-letters.edit', [
            'incomingLetterId' => $incomingLetter->id,
        ]);
    }

    private function buildIncomingLetterQuery(Request $request)
    {
        $ids = array_filter(
            array_map('intval', explode(',', $request->string('ids')->toString())),
            fn ($id) => $id > 0,
        );

        return IncomingLetter::query()
            ->with(['department', 'dispositionDepartment'])
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('sender', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('agenda_name', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            })
            ->when($request->integer('department_id') ?: null, fn ($query, $department) => $query->where('department_id', $department))
            ->when($request->integer('year') ?: null, fn ($query, $year) => $query->whereYear('received_date', $year))
            ->when($request->string('letter_attribute')->trim()->toString() ?: null, fn ($query, $attribute) => $query->where('letter_attribute', $attribute))
            ->when($request->string('status')->trim()->toString() ?: null, fn ($query, $status) => $query->where('status', $status))
            ->latest();
    }
}
