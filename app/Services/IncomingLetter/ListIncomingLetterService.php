<?php

namespace App\Services\IncomingLetter;

use App\Models\IncomingLetter;
use App\Support\ListOrder;

/**
 * Builds paginated incoming letter listing with search and filters.
 *
 * Related modules: IncomingLetterController, IncomingLetter, Department.
 */
class ListIncomingLetterService
{
    /**
     * @param  array<string, mixed>  $filters  search, year, department_id, letter_attribute, status, per_page, order.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function handle(array $filters = [])
    {
        $query = IncomingLetter::query()
            ->with(['department', 'dispositionDepartment', 'creator'])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(fn ($query) =>
                        $query->where('letter_number', 'like', "%{$search}%")
                            ->orWhere('sender', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('agenda_name', 'like', "%{$search}%")
                            ->orWhere('summary', 'like', "%{$search}%")
                    );
                }
            )
            ->when(
                $filters['year'] ?? null,
                fn ($query, $year) => $query->whereYear('received_date', $year)
            )
            ->when(
                $filters['department_id'] ?? null,
                fn ($query, $department) => $query->where('department_id', $department)
            )
            ->when(
                $filters['letter_attribute'] ?? null,
                fn ($query, $attribute) => $query->where('letter_attribute', $attribute)
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            );

        $query = ListOrder::apply($query, $filters['order'] ?? null, 'created_at');

        return $query->paginate($filters['per_page'] ?? 10);
    }
}
