@props([
    'rows' => 8,
    'columns' => 6,
    'class' => '',
])

<div {{ $attributes->class(['ds-skeleton-table', $class]) }} data-skeleton-root>
    <div class="ds-skeleton-table__header" aria-hidden="true">
        <div class="ds-skeleton-table__row ds-skeleton-table__row--header">
            @for ($column = 0; $column < $columns; $column++)
                <div class="ds-skeleton-table__cell">
                    <x-skeleton
                        class="ds-skeleton--bar"
                        style="width: {{ $column === 0 ? '40%' : (55 + (($column % 3) * 10)) . '%' }}"
                    />
                </div>
            @endfor
        </div>
    </div>

    <div class="ds-skeleton-table__body" aria-hidden="true">
        @for ($row = 0; $row < $rows; $row++)
            <div class="ds-skeleton-table__row">
                @for ($column = 0; $column < $columns; $column++)
                    <div class="ds-skeleton-table__cell">
                        <x-skeleton
                            class="ds-skeleton--bar"
                            style="width: {{ 45 + (($column % 4) * 12) }}%"
                        />
                    </div>
                @endfor
            </div>
        @endfor
    </div>

    <div class="ds-skeleton-table__pagination" aria-hidden="true">
        <x-skeleton class="ds-skeleton--bar" style="width: 28%" />
        <div class="ds-skeleton-table__pagination-actions">
            <x-skeleton class="ds-skeleton--bar" style="width: 3rem" />
            <x-skeleton class="ds-skeleton--bar" style="width: 3rem" />
            <x-skeleton class="ds-skeleton--bar" style="width: 3rem" />
        </div>
    </div>
</div>
