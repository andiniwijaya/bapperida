@props([
    'class' => '',
])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block {{ $class }}"
    {{ $attributes }}>
    @if (isset($trigger))
        <div @click="open = !open" class="cursor-pointer">
            {{ $trigger }}
        </div>
    @endif

    <div x-show="open" x-transition
        class="absolute left-0 mt-2 w-56 rounded-lg border border-charcoal-100 bg-white py-1 shadow-lg z-50 dark:border-navy-700 dark:bg-navy-800"
        x-cloak>
        {{ $slot }}
    </div>
</div>
