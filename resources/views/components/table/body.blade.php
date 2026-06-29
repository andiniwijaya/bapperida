@props([
    'class' => '',
])

<tbody class="divide-y divide-slate-200 dark:divide-navy-700 {{ $class }}">
    {{ $slot }}
</tbody>
