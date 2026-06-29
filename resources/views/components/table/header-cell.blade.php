@props([
    'sortable' => false,
    'sortDir' => null,
    'align' => 'left',
    'class' => '',
])

<th
    @class([
        'app-data-table__th',
        'app-data-table__th--center' => $align === 'center',
        'app-data-table__th--right' => $align === 'right',
        'app-data-table__th--sortable' => $sortable,
        $class,
    ])
>
    <div class="flex items-center gap-2 {{ $align === 'center' ? 'justify-center' : ($align === 'right' ? 'justify-end' : '') }}">
        {{ $slot }}

        @if ($sortable)
            <svg class="h-4 w-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                @if ($sortDir === 'asc')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4" />
                @elseif ($sortDir === 'desc')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3m0 0l4 4m0 0l-4-4" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4" />
                @endif
            </svg>
        @endif
    </div>
</th>
