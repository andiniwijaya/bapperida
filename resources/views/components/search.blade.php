@props([
    'id' => null,
    'name' => 'search',
    'value' => null,
    'placeholder' => 'Cari',
    'disabled' => false,
    'class' => '',
])

<div class="ds-search-wrap">
    <span class="ds-search-icon">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
        </svg>
    </span>

    <input type="search" id="{{ $id ?? $name }}" name="{{ $name }}" value="{{ $value }}"
        placeholder="{{ $placeholder }}" @disabled($disabled)
        class="ds-input ds-search-input {{ $class }}"
        {{ $attributes }} />
</div>
