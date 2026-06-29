@props([
    'count' => 4,
    'class' => '',
])

<div {{ $attributes->class(['ds-skeleton-cards', $class]) }} data-skeleton-root>
    @for ($index = 0; $index < $count; $index++)
        <div class="ds-skeleton-card">
            <x-skeleton variant="title" />
            <x-skeleton variant="value" />
            <x-skeleton class="ds-skeleton--bar ds-skeleton--hint" style="width: 55%" />
        </div>
    @endfor
</div>
