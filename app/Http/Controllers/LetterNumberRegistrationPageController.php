<?php

namespace App\Http\Controllers;

use App\Models\LetterNumberRegistration;
use App\Services\LetterNumberRegistration\ExportLetterNumberRegistrationPdfService;
use App\Support\RegistrationCardPrint;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterNumberRegistrationPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', LetterNumberRegistration::class);

        return view('letter-number-registrations.index');
    }

    public function create(): View
    {
        $this->authorize('create', LetterNumberRegistration::class);

        return view('letter-number-registrations.create');
    }

    public function print(Request $request): View
    {
        $this->authorize('viewAny', LetterNumberRegistration::class);

        $registrations = $this->buildRegistrationQuery($request)
            ->get();

        $options = $this->resolveCardPrintOptions($request);

        return view('letter-number-registrations.card-print', [
            'registrations' => $registrations,
            'pdfMode' => false,
            ...$options,
        ]);
    }

    public function exportPdf(Request $request, ExportLetterNumberRegistrationPdfService $service)
    {
        $this->authorize('viewAny', LetterNumberRegistration::class);

        $registrations = $this->buildRegistrationQuery($request)
            ->get();

        $options = $this->resolveCardPrintOptions($request);

        return $service->handle(
            $registrations,
            $options['layout'],
            $options['background'],
            $options['backgroundColor'],
        );
    }

    public function edit(int $letterNumberRegistration): View
    {
        $registration = LetterNumberRegistration::query()->findOrFail($letterNumberRegistration);

        $this->authorize('update', $registration);

        return view('letter-number-registrations.edit', [
            'registrationId' => $letterNumberRegistration,
        ]);
    }

    private function buildRegistrationQuery(Request $request)
    {
        $ids = array_filter(
            array_map('intval', explode(',', $request->string('ids')->toString())),
            fn ($id) => $id > 0,
        );

        return LetterNumberRegistration::query()
            ->with(['department', 'creator'])
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('department_id') ?: null, fn ($query, $department) => $query->where('department_id', $department))
            ->when($request->string('letter_type')->trim()->toString() ?: null, fn ($query, $type) => $query->where('letter_type', $type))
            ->when($request->string('status')->trim()->toString() ?: null, fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('year') ?: null, fn ($query, $year) => $query->where('year', $year))
            ->latest();
    }

    public function show(int $letterNumberRegistration): View
    {
        $registration = LetterNumberRegistration::query()->findOrFail($letterNumberRegistration);

        $this->authorize('view', $registration);

        return view('letter-number-registrations.show', [
            'registrationId' => $letterNumberRegistration,
        ]);
    }

    /**
     * @return array{
     *     layout: string,
     *     background: string,
     *     backgroundColor: string,
     *     layoutLabel: string,
     *     backgroundLabel: string
     * }
     */
    private function resolveCardPrintOptions(Request $request): array
    {
        $layout = RegistrationCardPrint::resolveLayout(
            $request->string('layout')->toString() ?: null,
        );
        $background = RegistrationCardPrint::resolveBackground(
            $request->string('background')->toString() ?: null,
        );

        return [
            'layout' => $layout,
            'background' => $background,
            'backgroundColor' => RegistrationCardPrint::backgroundColor($background, $layout),
            'layoutLabel' => RegistrationCardPrint::layoutOptions()[$layout],
            'backgroundLabel' => RegistrationCardPrint::backgroundOptions()[$background],
        ];
    }
}
