@props([
    'type' => 'success', // success, error, warning, info
    'message' => null,
    'duration' => 5000,
    'class' => '',
])

@php
    $typeMap = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
    ];

    $alertType = $typeMap[$type] ?? 'info';

    $icons = [
        'success' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'error' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'warning' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-10a8 8 0 100 16 8 8 0 000-16z" />',
        'info' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    ];
@endphp

<div x-data="{
    show: true,
    init() {
        @if ($duration > 0) setTimeout(() => { this.show = false }, {{ $duration }}) @endif
    }
}" @init="init()" x-show="show" x-transition
    class="fixed bottom-4 right-4 max-w-sm ds-alert ds-alert--{{ $alertType }} shadow-lg {{ $class }}"
    {{ $attributes }}>
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icons[$alertType] !!}
        </svg>

        <div class="flex-1 text-sm">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                {{ $message }}
            @endif
        </div>

        <button @click="show = false" class="hover:opacity-80 focus:outline-none focus-visible:ring-2"
            style="color: inherit;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
