@props([
    'hoverable' => false,
    'clickable' => false,
    'class' => '',
])

<div @class([
    'bg-white dark:bg-navy-800',
    'border border-charcoal-100 dark:border-navy-700',
    'rounded-xl shadow-sm',
    'transition-all duration-200',
    'hover:shadow-md hover:border-charcoal-200 dark:hover:border-navy-600' => $hoverable,
    'cursor-pointer' => $clickable,
    $class,
]) {{ $attributes }}>
    {{ $slot }}
</div>
