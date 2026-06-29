@props([
    'class' => '',
])

<div class="app-form-card {{ $class }}" {{ $attributes }}>
    {{ $slot }}
</div>
