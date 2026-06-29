@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'class' => '',
])

<div class="mb-6 {{ $class }}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            @if ($icon)
                <div class="rounded-lg bg-ocean-50 p-3 text-ocean-700 dark:bg-ocean-900/40 dark:text-gold-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $icon !!}
                    </svg>
                </div>
            @endif

            <div>
                @if ($title)
                    <h2 class="font-heading text-xl font-semibold text-charcoal-900 dark:text-slate-100 sm:text-2xl">
                        {{ $title }}
                    </h2>
                @endif

                @if ($description)
                    <p class="mt-1 text-sm leading-relaxed text-charcoal-600 dark:text-slate-400 sm:text-base">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>

        @if ($slot->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
