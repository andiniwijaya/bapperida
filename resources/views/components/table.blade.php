@props([
    'striped' => true,
    'hover' => true,
    'class' => '',
])

<table class="app-data-table min-w-full text-left text-sm {{ $class }}" {{ $attributes }}>
    @if (isset($head))
        <thead>
            {{ $head }}
        </thead>
    @endif

    {{ $slot }}
</table>
