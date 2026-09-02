<?php

namespace App\Support;

/**
 * Builds a compact page-number window for table pagination.
 *
 * Returns a list of page numbers with optional 'ellipsis' markers so the
 * control stays centered and readable when there are many pages.
 */
final class PaginationWindow
{
    /**
     * @return list<int|'ellipsis'>
     */
    public static function items(int $currentPage, int $lastPage, int $siblings = 2): array
    {
        if ($lastPage < 1) {
            return [];
        }

        $currentPage = max(1, min($currentPage, $lastPage));

        if ($lastPage <= 7) {
            return range(1, $lastPage);
        }

        $start = max(1, $currentPage - $siblings);
        $end = min($lastPage, $currentPage + $siblings);

        $pages = [];

        if ($start > 1) {
            $pages[] = 1;

            if ($start > 2) {
                $pages[] = 'ellipsis';
            }
        }

        for ($page = $start; $page <= $end; $page++) {
            $pages[] = $page;
        }

        if ($end < $lastPage) {
            if ($end < $lastPage - 1) {
                $pages[] = 'ellipsis';
            }

            $pages[] = $lastPage;
        }

        return $pages;
    }
}
