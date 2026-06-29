@props([
    'align' => 'left', // left, right
    'class' => '',
])

@php
    $alignClass = $align === 'right' ? 'right-0' : 'left-0';
@endphp

<div x-show="open" x-transition
    class="absolute {{ $alignClass }} mt-2 w-48 rounded-lg shadow-lg
        bg-white dark:bg-navy-800
        border border-charcoal-100 dark:border-navy-700
        py-1 z-50
        {{ $class }}"
    {{ $attributes }}>
    {{ $slot }}
</div>
