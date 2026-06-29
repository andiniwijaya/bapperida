@props([
    'class' => '',
])

<div class="app-filter-card {{ $class }}" {{ $attributes }}>
    {{ $slot }}
</div>
