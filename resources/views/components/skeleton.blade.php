@props([
    'variant' => 'bar',
    'class' => '',
])

@php
    $baseClass = 'ds-skeleton';
    $variantClass = match ($variant) {
        'bar' => 'ds-skeleton--bar',
        'title' => 'ds-skeleton--bar ds-skeleton--title',
        'value' => 'ds-skeleton--bar ds-skeleton--value',
        'chart' => 'ds-skeleton--chart-area',
        default => 'ds-skeleton--bar',
    };
@endphp

<div {{ $attributes->class([$baseClass, $variantClass, $class]) }} aria-hidden="true"></div>
