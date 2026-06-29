@props([
    'title' => null,
    'description' => null,
    'class' => '',
])

<div class="app-section-header {{ $class }}">
    @if ($title)
        <h2 class="app-section-header__title">{{ $title }}</h2>
    @endif

    @if ($description)
        <p class="app-section-header__description">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-3">{{ $slot }}</div>
    @endif
</div>
