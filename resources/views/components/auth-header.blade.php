@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-2 text-center">
    <h1 class="font-heading text-2xl font-semibold text-charcoal-900 dark:text-slate-100">{{ $title }}</h1>
    @if ($description)
        <p class="text-sm leading-relaxed text-charcoal-600 dark:text-slate-400">{{ $description }}</p>
    @endif
</div>
