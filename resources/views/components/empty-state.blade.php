@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
    'page' => null,
    'class' => '',
])

<div
    {{ $attributes->class(['ds-empty-state']) }}
    @if ($page) data-empty-page="{{ $page }}" @endif
    data-empty-state-root
>
    <div class="ds-empty-state__icon" data-empty-state-icon aria-hidden="true">
        <i data-lucide="{{ $icon }}" class="h-12 w-12"></i>
    </div>

    <h3 class="ds-empty-state__title" data-empty-state-title>
        {{ $title }}
    </h3>

    <p class="ds-empty-state__description" data-empty-state-description @if (!$description) hidden @endif>
        {{ $description }}
    </p>

    <div class="ds-empty-state__actions" data-empty-state-actions>
        {{ $slot }}
    </div>
</div>
