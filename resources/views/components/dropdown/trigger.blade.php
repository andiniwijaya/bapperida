@props([
    'class' => '',
])

<button @click="open = !open"
    class="inline-flex items-center p-2 rounded-lg
        hover:bg-slate-100 dark:hover:bg-navy-700
        text-slate-600 dark:text-slate-400
        transition-colors duration-200
        {{ $class }}"
    {{ $attributes }}>
    {{ $slot }}
</button>
