<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Normalizes list ordering for paginated API indexes (terbaru / terlama).
 */
final class ListOrder
{
    public const LATEST = 'latest';

    public const OLDEST = 'oldest';

    public static function direction(?string $order, string $default = self::LATEST): string
    {
        $resolved = $order ?? $default;

        return $resolved === self::OLDEST ? 'asc' : 'desc';
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public static function apply(Builder $query, ?string $order, string $column = 'created_at', string $default = self::LATEST): Builder
    {
        return $query->orderBy($column, self::direction($order, $default));
    }
}
