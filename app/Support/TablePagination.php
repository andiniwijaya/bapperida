<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * Shared per-page options for CRUD table listings.
 */
final class TablePagination
{
    public const DEFAULT_PER_PAGE = 10;

    /**
     * @var list<int>
     */
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    /**
     * @return list<string|In>
     */
    public static function rules(): array
    {
        return ['nullable', 'integer', Rule::in(self::PER_PAGE_OPTIONS)];
    }

    public static function resolve(?int $perPage): int
    {
        if ($perPage !== null && in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }

        return self::DEFAULT_PER_PAGE;
    }
}
