@props([
    'title' => null,
    'description' => null,
    'formId' => null,
    'method' => 'POST',
    'enctype' => null,
])

<form
    @if ($formId) id="{{ $formId }}" @endif
    method="{{ $method }}"
    @if ($enctype) enctype="{{ $enctype }}" @endif
    data-form-ux
    {{ $attributes->class(['app-crud-form-card']) }}
>
    @if ($title)
        <div class="app-crud-form-card__header">
            <div>
                <h2 class="app-crud-form-card__title">{{ $title }}</h2>
                @if ($description)
                    <p class="app-crud-form-card__description">{{ $description }}</p>
                @endif
            </div>
        </div>
    @endif

    <div class="app-crud-form-card__body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="app-crud-form-card__footer">
            {{ $footer }}
        </div>
    @endisset
</form>
