@props([
    'title' => null,
    'description' => null,
    'class' => '',
])

<div @class(['app-panel', $class]) {{ $attributes }}>
    @if ($title || isset($header))
        <div @class(['app-panel__header', 'app-panel__header--split' => isset($header)])>
            <div>
                @if ($title)
                    <h2 class="app-panel__title">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="app-panel__description">{{ $description }}</p>
                @endif
            </div>

            @isset($header)
                <div class="app-panel__actions">{{ $header }}</div>
            @endisset
        </div>
    @endif

    <div class="app-panel__body">
        {{ $slot }}
    </div>
</div>
