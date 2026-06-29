@props([
    'class' => '',
])

<td class="px-6 py-4 text-slate-900 dark:text-slate-100 {{ $class }}">
    {{ $slot }}
</td>
