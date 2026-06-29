@props([
    'class' => '',
])

<thead class="app-data-table__thead {{ $class }}">
    {{ $slot }}
</thead>
