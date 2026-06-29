@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success, warning, info, ghost, outline
    'size' => 'md', // sm, md, lg
    'disabled' => false,
    'loading' => false,
    'loadingText' => 'Memproses...',
    'icon' => null,
    'iconOnly' => false,
    'href' => null,
    'target' => null,
    'rel' => null,
    'class' => '',
])

@php
    $variants = [
        'primary' => 'ds-btn--primary',
        'secondary' => 'ds-btn--secondary',
        'danger' => 'ds-btn--danger',
        'success' => 'ds-btn--success',
        'warning' => 'ds-btn--warning',
        'info' => 'ds-btn--info',
        'ghost' => 'ds-btn--ghost',
        'outline' => 'ds-btn--outline',
    ];

    $sizes = [
        'sm' => 'ds-btn--sm',
        'md' => '',
        'lg' => 'ds-btn--lg',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? '';
    $classes =
        'ds-btn ' .
        $variantClass .
        ' ' .
        $sizeClass .
        ' ' .
        ($iconOnly ? 'ds-btn--icon-only' : '') .
        ' ' .
        $class;
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($target) target="{{ $target }}" @endif
        @if ($rel) rel="{{ $rel }}" @endif aria-busy="{{ $loading ? 'true' : 'false' }}"
        aria-disabled="{{ $disabled || $loading ? 'true' : 'false' }}" class="{{ $classes }}" {{ $attributes }}>
        @if ($loading)
            <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            @if (!$iconOnly)
                <span>{{ $loadingText }}</span>
            @endif
        @elseif ($icon)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif

        @if (!$iconOnly && !$loading)
            {{ $slot }}
        @endif
    </a>
@else
    <button type="{{ $type }}" @disabled($disabled || $loading) aria-busy="{{ $loading ? 'true' : 'false' }}"
        aria-disabled="{{ $disabled || $loading ? 'true' : 'false' }}" class="{{ $classes }}"
        {{ $attributes }}>
        @if ($loading)
            <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            @if (!$iconOnly)
                <span>{{ $loadingText }}</span>
            @endif
        @elseif ($icon)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif

        @if (!$iconOnly && !$loading)
            {{ $slot }}
        @endif
    </button>
@endif
