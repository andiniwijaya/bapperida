@props([
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'class' => '',
])

@if ($href)
    <a href="{{ $href }}"
        class="flex items-center gap-3 px-4 py-2 text-sm text-slate-900 dark:text-slate-100
            hover:bg-slate-100 dark:hover:bg-navy-700
            transition-colors duration-200
            {{ $class }}"
        {{ $attributes }}>
        @if ($icon)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-900 dark:text-slate-100
            hover:bg-slate-100 dark:hover:bg-navy-700
            transition-colors duration-200
            text-left
            {{ $class }}"
        {{ $attributes }}>
        @if ($icon)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
