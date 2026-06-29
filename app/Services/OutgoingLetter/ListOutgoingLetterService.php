<?php

namespace App\Services\OutgoingLetter;

use App\Models\OutgoingLetter;
use App\Support\ListOrder;

/**
 * Builds paginated, filterable queries for outgoing letter listing.
 *
 * Related modules: OutgoingLetter, OutgoingLetterController.
 */
class ListOutgoingLetterService
{
    /**
     * Execute filtered query with registration and creator eager loads.
     *
     * @param  array{search?: string, year?: int, department_id?: int, letter_type?: string, status?: string, per_page?: int}  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function handle(array $filters = [])
    {
        $query = OutgoingLetter::query()
            ->with(['registration.department', 'creator'])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->whereHas('registration', function ($query) use ($search) {
                        $query->where('letter_number', 'like', "%{$search}%")
                            ->orWhere('index_code', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('recipient', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $filters['year'] ?? null,
                fn ($query, $year) => $query->whereHas('registration', fn ($query) => $query->where('year', $year))
            )
            ->when(
                $filters['department_id'] ?? null,
                fn ($query, $department) => $query->whereHas('registration', fn ($query) => $query->where('department_id', $department))
            )
            ->when(
                $filters['letter_type'] ?? null,
                fn ($query, $type) => $query->where('letter_type', $type)
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            );

        $query = ListOrder::apply($query, $filters['order'] ?? null, 'created_at');

        return $query->paginate($filters['per_page'] ?? 10);
    }
}
