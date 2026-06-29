@props([
    'class' => '',
])

<tr class="hover:bg-slate-50 dark:hover:bg-navy-700/50 transition-colors duration-200 {{ $class }}">
    {{ $slot }}
</tr>
