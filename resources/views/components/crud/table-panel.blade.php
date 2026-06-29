@props([
    'title',
    'description' => null,
])

<x-panel :title="$title" :description="$description" {{ $attributes->class(['crud-table-panel']) }}>
    <div class="crud-table-panel__content">
        @isset($toolbar)
            <div class="crud-table-panel__toolbar">
                {{ $toolbar }}
            </div>
        @endisset

        <div class="crud-table-panel__table-area">
            {{ $slot }}
        </div>
    </div>
</x-panel>
