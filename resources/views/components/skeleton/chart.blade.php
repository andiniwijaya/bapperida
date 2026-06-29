@props([
    'count' => 2,
    'class' => '',
])

<div {{ $attributes->class(['ds-skeleton-charts', $class]) }} data-skeleton-root>
    @for ($index = 0; $index < $count; $index++)
        <div class="ds-skeleton-chart">
            <x-skeleton class="ds-skeleton--bar ds-skeleton--chart-title" />
            <x-skeleton class="ds-skeleton--bar ds-skeleton--chart-subtitle" />
            <x-skeleton variant="chart" />
        </div>
    @endfor
</div>
