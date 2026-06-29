@props([
    'color' => 'neutral', // neutral, success, danger, warning, info, gold, gray
    'variant' => 'solid', // solid only (outline maps to same tokens)
    'size' => 'md', // sm, md, lg
    'rounded' => true,
    'class' => '',
])

@php
    $colorMap = [
        'gray' => 'neutral',
        'slate' => 'neutral',
        'green' => 'success',
        'red' => 'danger',
        'blue' => 'info',
        'gold' => 'gold',
        'success' => 'success',
        'danger' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
        'neutral' => 'neutral',
    ];

    $dsColor = $colorMap[$color] ?? 'neutral';

    $sizes = [
        'sm' => 'text-xs px-2 py-0.5',
        'md' => '',
        'lg' => 'text-sm px-4 py-1.5',
    ];

    $sizeClass = $sizes[$size] ?? '';
@endphp

<span
    class="ds-badge ds-badge--{{ $dsColor }} {{ $sizeClass }} {{ $rounded ? '' : '!rounded-md' }} {{ $class }}"
    {{ $attributes }}>
    {{ $slot }}
</span>
