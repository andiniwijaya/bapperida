<?php

namespace App\Services\Report;

use App\Models\IncomingLetter;
use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use App\Support\ListOrder;
use App\Support\ReportExportSchema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds paginated report rows across letter modules with search and filters.
 *
 * Business rules:
 * - Single-type reports paginate at the database layer.
 * - Combined (all) reports use a union subquery for scalable pagination.
 * - Status and letter type filters align with statistics service semantics.
 *
 * Related modules: ReportController, ReportPageController, export services.
 */
class ListReportService
{
    /**
     * @param  array<string, mixed>  $filters  report_type, search, department_id, year, month, user_id, status, letter_type, period bounds, per_page, page.
     * @return LengthAwarePaginator|Collection<int, array<string, mixed>>
     */
    public function handle(array $filters = []): LengthAwarePaginator|Collection
    {
        $filters = $this->normalizeFilters($filters);
        $reportType = $filters['report_type'];
        $perPage = $filters['per_page'];
        $page = $filters['page'];

        if ($reportType === 'registration') {
            if ($perPage === null) {
                return $this->mapRegistrationRows($this->registrationQuery($filters)->get());
            }

            return $this->paginateRegistration($filters, $perPage, $page);
        }

        if ($reportType === 'incoming') {
            if ($perPage === null) {
                return $this->mapIncomingRows($this->incomingQuery($filters)->get());
            }

            return $this->paginateIncoming($filters, $perPage, $page);
        }

        if ($reportType === 'outgoing') {
            if ($perPage === null) {
                return $this->mapOutgoingRows($this->outgoingQuery($filters)->get());
            }

            return $this->paginateOutgoing($filters, $perPage, $page);
        }

        if ($perPage === null) {
            return $this->mapRegistrationRows($this->registrationQuery($filters)->get())
                ->concat($this->mapIncomingRows($this->incomingQuery($filters)->get()))
                ->concat($this->mapOutgoingRows($this->outgoingQuery($filters)->get()))
                ->sortBy('date', SORT_REGULAR, ListOrder::direction($filters['order']) === 'desc')
                ->values();
        }

        return $this->paginateAllTypes($filters, $perPage, $page);
    }

    /**
     * Column headings for print/PDF export by report type.
     *
     * @return array<int, string>
     */
    public function columnsForReportType(string $reportType): array
    {
        return ReportExportSchema::headings($reportType);
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function exportColumnsForReportType(string $reportType): array
    {
        return ReportExportSchema::columns($reportType);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginateRegistration(array $filters, ?int $perPage, int $page): LengthAwarePaginator
    {
        $paginator = $this->registrationQuery($filters)
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection($this->mapRegistrationRows($paginator->getCollection()));

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginateIncoming(array $filters, ?int $perPage, int $page): LengthAwarePaginator
    {
        $paginator = $this->incomingQuery($filters)
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection($this->mapIncomingRows($paginator->getCollection()));

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginateOutgoing(array $filters, ?int $perPage, int $page): LengthAwarePaginator
    {
        $paginator = $this->outgoingQuery($filters)
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection($this->mapOutgoingRows($paginator->getCollection()));

        return $paginator;
    }

    /**
     * Union-based pagination for combined report types.
     *
     * @param  array<string, mixed>  $filters
     */
    private function paginateAllTypes(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        $registrationUnion = $this->unionRegistrationQuery($filters)
            ->selectRaw('id, ? as report_type, letter_date as sort_date', ['registration']);

        $incomingUnion = $this->unionIncomingQuery($filters)
            ->selectRaw('id, ? as report_type, received_date as sort_date', ['incoming']);

        $outgoingUnion = $this->unionOutgoingQuery($filters)
            ->selectRaw('outgoing_letters.id as id, ? as report_type, letter_number_registrations.letter_date as sort_date', ['outgoing']);

        $union = $registrationUnion->unionAll($incomingUnion)->unionAll($outgoingUnion);

        $total = DB::query()->fromSub($union, 'merged_reports')->count();

        $direction = ListOrder::direction($filters['order'] ?? null);

        $slice = DB::query()
            ->fromSub($union, 'merged_reports')
            ->orderBy('sort_date', $direction)
            ->forPage($page, $perPage)
            ->get();

        $rows = collect();

        foreach ($slice as $item) {
            $row = match ($item->report_type) {
                'registration' => $this->mapRegistrationRows(
                    LetterNumberRegistration::query()->with('department')->whereKey($item->id)->get()
                )->first(),
                'incoming' => $this->mapIncomingRows(
                    IncomingLetter::query()->with('department')->whereKey($item->id)->get()
                )->first(),
                'outgoing' => $this->mapOutgoingRows(
                    OutgoingLetter::query()->with(['registration.department'])->whereKey($item->id)->get()
                )->first(),
                default => null,
            };

            if ($row !== null) {
                $rows->push($row);
            }
        }

        return new Paginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );
    }

    /**
     * Union subquery without eager loads for combined pagination.
     *
     * @param  array<string, mixed>  $filters
     */
    private function unionRegistrationQuery(array $filters): Builder
    {
        return LetterNumberRegistration::query()
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'], fn ($query, $year) => $query->where('year', $year))
            ->when($filters['month'], fn ($query, $month) => $query->whereMonth('letter_date', $month))
            ->when($filters['period_start'], fn ($query, $start) => $query->whereDate('letter_date', '>=', $start))
            ->when($filters['period_end'], fn ($query, $end) => $query->whereDate('letter_date', '<=', $end))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'], fn ($query, $type) => $query->where('letter_type', $type));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function unionIncomingQuery(array $filters): Builder
    {
        return IncomingLetter::query()
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('sender', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('agenda_name', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'], fn ($query, $year) => $query->whereYear('received_date', $year))
            ->when($filters['month'], fn ($query, $month) => $query->whereMonth('received_date', $month))
            ->when($filters['period_start'], fn ($query, $start) => $query->whereDate('received_date', '>=', $start))
            ->when($filters['period_end'], fn ($query, $end) => $query->whereDate('received_date', '<=', $end))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'], fn ($query, $type) => $query->where('letter_attribute', $type));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function unionOutgoingQuery(array $filters): Builder
    {
        return OutgoingLetter::query()
            ->join('letter_number_registrations', 'outgoing_letters.letter_number_registration_id', '=', 'letter_number_registrations.id')
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('letter_number_registrations.letter_number', 'like', "%{$search}%")
                        ->orWhere('letter_number_registrations.index_code', 'like', "%{$search}%")
                        ->orWhere('letter_number_registrations.letter_code', 'like', "%{$search}%")
                        ->orWhere('letter_number_registrations.subject', 'like', "%{$search}%")
                        ->orWhere('letter_number_registrations.recipient', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->where('letter_number_registrations.department_id', $departmentId))
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('outgoing_letters.created_by', $userId))
            ->when($filters['year'], fn ($query, $year) => $query->where('letter_number_registrations.year', $year))
            ->when($filters['month'], fn ($query, $month) => $query->whereMonth('letter_number_registrations.letter_date', $month))
            ->when($filters['period_start'], fn ($query, $start) => $query->whereDate('letter_number_registrations.letter_date', '>=', $start))
            ->when($filters['period_end'], fn ($query, $end) => $query->whereDate('letter_number_registrations.letter_date', '<=', $end))
            ->when($filters['status'], fn ($query, $status) => $query->where('outgoing_letters.status', $status))
            ->when($filters['letter_type'], fn ($query, $type) => $query->where('outgoing_letters.letter_type', $type));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function registrationQuery(array $filters): Builder
    {
        $query = LetterNumberRegistration::query()
            ->with(['department', 'creator', 'updater', 'deleter'])
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'], fn ($query, $year) => $query->where('year', $year))
            ->when($filters['month'], fn ($query, $month) => $query->whereMonth('letter_date', $month))
            ->when($filters['period_start'], fn ($query, $start) => $query->whereDate('letter_date', '>=', $start))
            ->when($filters['period_end'], fn ($query, $end) => $query->whereDate('letter_date', '<=', $end))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'], fn ($query, $type) => $query->where('letter_type', $type));

        return ListOrder::apply($query, $filters['order'] ?? null, 'letter_date');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function incomingQuery(array $filters): Builder
    {
        $query = IncomingLetter::query()
            ->with(['department', 'dispositionDepartment', 'creator', 'updater', 'deleter'])
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('sender', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('agenda_name', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'], fn ($query, $year) => $query->whereYear('received_date', $year))
            ->when($filters['month'], fn ($query, $month) => $query->whereMonth('received_date', $month))
            ->when($filters['period_start'], fn ($query, $start) => $query->whereDate('received_date', '>=', $start))
            ->when($filters['period_end'], fn ($query, $end) => $query->whereDate('received_date', '<=', $end))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'], fn ($query, $type) => $query->where('letter_attribute', $type));

        return ListOrder::apply($query, $filters['order'] ?? null, 'received_date');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function outgoingQuery(array $filters): Builder
    {
        $query = OutgoingLetter::query()
            ->with(['registration.department', 'creator', 'updater', 'deleter'])
            ->when($filters['search'], function ($query, $search) {
                $query->whereHas('registration', function ($subQuery) use ($search) {
                    $subQuery->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('index_code', 'like', "%{$search}%")
                        ->orWhere('letter_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('recipient', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->whereHas('registration', fn ($subQuery) => $subQuery->where('department_id', $departmentId)))
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['year'], fn ($query, $year) => $query->whereHas('registration', fn ($subQuery) => $subQuery->where('year', $year)))
            ->when($filters['month'], fn ($query, $month) => $query->whereHas('registration', fn ($subQuery) => $subQuery->whereMonth('letter_date', $month)))
            ->when($filters['period_start'], fn ($query, $start) => $query->whereHas('registration', fn ($subQuery) => $subQuery->whereDate('letter_date', '>=', $start)))
            ->when($filters['period_end'], fn ($query, $end) => $query->whereHas('registration', fn ($subQuery) => $subQuery->whereDate('letter_date', '<=', $end)))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['letter_type'], fn ($query, $type) => $query->where('letter_type', $type));

        return ListOrder::apply($query, $filters['order'] ?? null, 'created_at');
    }

    /**
     * @param  Collection<int, LetterNumberRegistration>  $registrations
     */
    private function mapRegistrationRows(Collection $registrations): Collection
    {
        return $registrations->map(function (LetterNumberRegistration $registration) {
            return [
                'id' => $registration->id,
                'type' => 'registration',
                'type_label' => 'Registrasi Penomoran',
                'letter_number' => $registration->letter_number,
                'index_code' => $registration->index_code,
                'letter_code' => $registration->letter_code,
                'sequence_number' => $registration->sequence_number,
                'year' => $registration->year,
                'date' => $registration->letter_date?->format('Y-m-d'),
                'letter_date' => $this->formatDate($registration->letter_date),
                'department' => $registration->department?->name,
                'department_id' => $registration->department_id,
                'origin_destination' => $registration->recipient,
                'subject' => $registration->subject,
                'status_label' => config('status.letter_registration')[$registration->status] ?? $registration->status,
                'attachment' => $registration->attachment,
                'letter_type_label' => config('letter.types')[$registration->letter_type] ?? $registration->letter_type,
                'agenda_name' => null,
                'summary' => $registration->summary,
                'sender' => null,
                'recipient' => $registration->recipient,
                'file_name' => null,
                'file_path' => null,
                'notes' => $registration->notes,
                'created_by' => $registration->creator?->name,
                'updated_by' => $registration->updater?->name,
                'deleted_by' => $registration->deleter?->name,
                'created_at' => $this->formatDateTime($registration->created_at),
                'updated_at' => $this->formatDateTime($registration->updated_at),
                'deleted_at' => $this->formatDateTime($registration->deleted_at),
            ];
        });
    }

    /**
     * @param  Collection<int, IncomingLetter>  $incomingLetters
     */
    private function mapIncomingRows(Collection $incomingLetters): Collection
    {
        return $incomingLetters->map(function (IncomingLetter $incomingLetter) {
            return [
                'id' => $incomingLetter->id,
                'type' => 'incoming',
                'type_label' => 'Arsip Surat Masuk',
                'letter_number' => $incomingLetter->letter_number,
                'index_code' => null,
                'letter_code' => null,
                'sequence_number' => null,
                'year' => $incomingLetter->received_date?->format('Y') ?? null,
                'date' => $incomingLetter->received_date?->format('Y-m-d'),
                'sent_date' => $this->formatDate($incomingLetter->sent_date),
                'received_date' => $this->formatDate($incomingLetter->received_date),
                'disposition_date' => $this->formatDate($incomingLetter->disposition_date),
                'department' => $incomingLetter->department?->name,
                'department_id' => $incomingLetter->department_id,
                'disposition_department' => $incomingLetter->dispositionDepartment?->name,
                'disposition_department_id' => $incomingLetter->disposition_department_id,
                'origin_destination' => $incomingLetter->sender,
                'subject' => $incomingLetter->subject,
                'status_label' => config('status.incoming_letter')[$incomingLetter->status] ?? $incomingLetter->status,
                'attachment' => $incomingLetter->attachment,
                'letter_type_label' => config('letter.types')[$incomingLetter->letter_attribute] ?? $incomingLetter->letter_attribute,
                'agenda_name' => $incomingLetter->agenda_name,
                'summary' => $incomingLetter->summary,
                'sender' => $incomingLetter->sender,
                'recipient' => null,
                'file_name' => $incomingLetter->file_path ? basename($incomingLetter->file_path) : null,
                'file_path' => $incomingLetter->file_path,
                'notes' => $incomingLetter->notes,
                'created_by' => $incomingLetter->creator?->name,
                'updated_by' => $incomingLetter->updater?->name,
                'deleted_by' => $incomingLetter->deleter?->name,
                'created_at' => $this->formatDateTime($incomingLetter->created_at),
                'updated_at' => $this->formatDateTime($incomingLetter->updated_at),
                'deleted_at' => $this->formatDateTime($incomingLetter->deleted_at),
            ];
        });
    }

    /**
     * @param  Collection<int, OutgoingLetter>  $outgoingLetters
     */
    private function mapOutgoingRows(Collection $outgoingLetters): Collection
    {
        return $outgoingLetters->map(function (OutgoingLetter $outgoingLetter) {
            $registration = $outgoingLetter->registration;

            return [
                'id' => $outgoingLetter->id,
                'type' => 'outgoing',
                'type_label' => 'Arsip Surat Keluar',
                'letter_number_registration_id' => $outgoingLetter->letter_number_registration_id,
                'letter_number' => $registration?->letter_number,
                'index_code' => $registration?->index_code,
                'letter_code' => $registration?->letter_code,
                'sequence_number' => $registration?->sequence_number,
                'year' => $registration?->year,
                'date' => $registration?->letter_date?->format('Y-m-d'),
                'letter_date' => $this->formatDate($registration?->letter_date),
                'department' => $registration?->department?->name,
                'origin_destination' => $registration?->recipient,
                'subject' => $registration?->subject,
                'status_label' => config('status.outgoing_letter')[$outgoingLetter->status] ?? $outgoingLetter->status,
                'attachment' => $outgoingLetter->attachment,
                'letter_type_label' => config('letter.types')[$outgoingLetter->letter_type] ?? $outgoingLetter->letter_type,
                'agenda_name' => null,
                'summary' => $registration?->summary,
                'sender' => null,
                'recipient' => $registration?->recipient,
                'file_name' => $outgoingLetter->file_path ? basename($outgoingLetter->file_path) : null,
                'file_path' => $outgoingLetter->file_path,
                'notes' => $outgoingLetter->notes,
                'created_by' => $outgoingLetter->creator?->name,
                'updated_by' => $outgoingLetter->updater?->name,
                'deleted_by' => $outgoingLetter->deleter?->name,
                'created_at' => $this->formatDateTime($outgoingLetter->created_at),
                'updated_at' => $this->formatDateTime($outgoingLetter->updated_at),
                'deleted_at' => $this->formatDateTime($outgoingLetter->deleted_at),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function normalizeFilters(array $filters): array
    {
        return array_merge([
            'report_type' => 'all',
            'search' => null,
            'department_id' => null,
            'user_id' => null,
            'year' => null,
            'month' => null,
            'period_start' => null,
            'period_end' => null,
            'status' => null,
            'letter_type' => null,
            'per_page' => 10,
            'page' => 1,
            'order' => 'latest',
        ], $filters);
    }

    private function formatDate(?\Carbon\CarbonInterface $value): ?string
    {
        return $value?->format('d/m/Y');
    }

    private function formatDateTime(?\Carbon\CarbonInterface $value): ?string
    {
        return $value?->format('d/m/Y H:i');
    }
}
