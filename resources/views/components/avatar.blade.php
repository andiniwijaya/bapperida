@props([
    'src' => null,
    'name' => null,
    'size' => 'md', // sm, md, lg, xl
    'initials' => null,
    'class' => '',
])

@php
    $sizes = [
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-lg',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    if (!$initials && $name) {
        $parts = preg_split('/\s+/', trim($name));
        $parts = array_values(array_filter($parts));
        $initials = isset($parts[1]) ? $parts[0] : substr($parts[0], 0, 2);
    }
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}"
        class="inline-block {{ $sizeClass }} rounded-full object-cover {{ $class }}" {{ $attributes }} />
@else
    <div class="inline-flex items-center justify-center {{ $sizeClass }} rounded-full font-semibold
            bg-gradient-to-br from-gold-400 to-gold-600
            text-white
            {{ $class }}"
        {{ $attributes }}>
        {{ $initials ?? '?' }}
    </div>
@endif
