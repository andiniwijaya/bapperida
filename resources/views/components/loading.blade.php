@props([
    'text' => null,
    'variant' => 'inline',
    'class' => '',
])

@php
    $variants = [
        'inline' => 'ds-skeleton-inline',
        'table' => 'ds-skeleton-table-wrap',
    ];

    $variantClass = $variants[$variant] ?? $variants['inline'];
@endphp

<div
    {{ $attributes->class([$variantClass, $class]) }}
    role="status"
    aria-live="polite"
    aria-busy="true"
>
    @if ($variant === 'table')
        <x-skeleton.table />
    @else
        <div class="ds-skeleton-inline__content">
            <x-skeleton class="ds-skeleton--bar" style="width: 60%" />
            <x-skeleton class="ds-skeleton--bar" style="width: 40%" />
        </div>
    @endif

    @if ($text)
        <p class="ds-skeleton-inline__text">{{ $text }}</p>
    @endif
</div>
