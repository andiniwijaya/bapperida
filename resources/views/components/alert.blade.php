@props([
    'type' => 'info', // info, success, warning, error
    'title' => null,
    'dismissible' => false,
    'icon' => null,
    'class' => '',
])

@php
    $typeMap = [
        'info' => 'info',
        'success' => 'success',
        'warning' => 'warning',
        'error' => 'error',
    ];

    $alertType = $typeMap[$type] ?? 'info';

    $icons = [
        'info' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'success' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'warning' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-10a8 8 0 100 16 8 8 0 000-16z" />',
        'error' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    ];
@endphp

<div x-data="{ open: true }" x-show="open"
    class="ds-alert ds-alert--{{ $alertType }} {{ $class }}">
    <div class="flex items-start">
        @if (!$icon)
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                {!! $icons[$alertType] !!}
            </svg>
        @else
            {{ $icon }}
        @endif

        <div class="ml-3">
            @if ($title)
                <h3 class="text-sm font-semibold">{{ $title }}</h3>
            @endif

            <div class="text-sm {{ $title ? 'mt-1' : '' }}">
                {{ $slot }}
            </div>
        </div>

        @if ($dismissible)
            <button @click="open = false" type="button"
                class="ml-3 -mr-2 inline-flex ds-caption hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                style="color: inherit;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
