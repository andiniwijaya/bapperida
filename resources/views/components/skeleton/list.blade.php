@props([
    'count' => 5,
    'class' => '',
])

<div {{ $attributes->class(['ds-skeleton-list', $class]) }} data-skeleton-root aria-busy="true">
    @for ($index = 0; $index < $count; $index++)
        <div class="ds-skeleton-list__item">
            <x-skeleton class="ds-skeleton--bar ds-skeleton--list-title" />
            <x-skeleton class="ds-skeleton--bar ds-skeleton--list-body" />
            <x-skeleton class="ds-skeleton--bar ds-skeleton--list-meta" />
        </div>
    @endfor
</div>
