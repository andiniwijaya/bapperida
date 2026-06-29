<?php

namespace App\Services\LetterNumberRegistration;

use App\Models\LetterNumberRegistration;
use App\Support\ListOrder;

/**
 * Builds paginated, filterable queries for letter number registration listing.
 *
 * Related modules: LetterNumberRegistration, LetterNumberRegistrationController.
 */
class ListLetterNumberRegistrationService
{
    /**
     * Execute filtered query with eager-loaded relations.
     *
     * @param  array{search?: string, department_id?: int, status?: string, letter_type?: string, year?: int, per_page?: int}  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function handle(array $filters = [])
    {
        $query = LetterNumberRegistration::query()
            ->with([
                'department',
                'creator',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('letter_number', 'like', "%{$search}%")
                            ->orWhere('index_code', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('recipient', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $filters['department_id'] ?? null,
                fn ($q, $department) => $q->where('department_id', $department)
            )
            ->when(
                $filters['status'] ?? null,
                fn ($q, $status) => $q->where('status', $status)
            )
            ->when(
                $filters['letter_type'] ?? null,
                fn ($q, $type) => $q->where('letter_type', $type)
            )
            ->when(
                $filters['year'] ?? null,
                fn ($q, $year) => $q->where('year', $year)
            );

        $query = ListOrder::apply($query, $filters['order'] ?? null, 'created_at');

        return $query->paginate($filters['per_page'] ?? 10);
    }
}