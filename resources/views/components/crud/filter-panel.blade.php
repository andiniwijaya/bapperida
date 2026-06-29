@props([
    'title' => 'Filter',
    'resetId' => 'reset-filter',
    'showReset' => true,
])

<x-panel :title="$title" {{ $attributes }}>
    @if ($showReset)
        <x-slot:header>
            <button
                type="button"
                id="{{ $resetId }}"
                class="app-icon-action app-icon-action--on-panel"
                data-app-tooltip="Atur Ulang"
                aria-label="Atur Ulang"
            >
                <i data-lucide="rotate-ccw" class="h-4 w-4" aria-hidden="true"></i>
            </button>
        </x-slot:header>
    @endif

    {{ $slot }}
</x-panel>
