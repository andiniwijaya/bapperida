@props([
    'title' => null,
    'value' => null,
    'subtitle' => null,
    'icon' => null,
    'trend' => null, // up, down
    'trendPercent' => null,
    'highlight' => false,
    'class' => '',
])

<div @class([
    'rounded-xl border p-6 shadow-sm transition duration-200',
    'border-charcoal-100 bg-white hover:border-ocean-200 hover:shadow-md dark:border-navy-700 dark:bg-navy-800 dark:hover:border-ocean-600' => ! $highlight,
    'border-maroon-200 bg-maroon-50 dark:border-maroon-800 dark:bg-maroon-950/30' => $highlight,
    $class,
])>
    <div class="flex items-start justify-between gap-3">
        <div>
            @if ($title)
                <p class="text-sm font-medium text-charcoal-500 dark:text-slate-400">{{ $title }}</p>
            @endif

            <div class="mt-2 flex items-baseline gap-2">
                <p class="text-3xl font-bold text-charcoal-900 dark:text-slate-100">{{ $value }}</p>

                @if ($trend)
                    <div
                        class="flex items-center gap-1 text-sm font-medium {{ $trend === 'up' ? 'text-ocean-600 dark:text-gold-400' : 'text-maroon-600 dark:text-maroon-400' }}"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            @if ($trend === 'up')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3m0 0l4 4m0 0l-4-4" />
                            @endif
                        </svg>
                        {{ $trendPercent }}%
                    </div>
                @endif
            </div>

            @if ($subtitle)
                <p class="mt-1 text-sm text-charcoal-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($icon)
            <div class="rounded-lg bg-ocean-50 p-2.5 text-ocean-700 dark:bg-ocean-900/40 dark:text-gold-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
    </div>
</div>
