@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
    'disabled' => false,
    'error' => null,
    'required' => false,
    'label' => null,
    'hint' => null,
    'class' => '',
])

@php
    $controlId = $id ?? $name;
    $describedBy = [];
    if ($error) {
        $describedBy[] = $controlId . '-error';
    }
    if ($hint) {
        $describedBy[] = $controlId . '-hint';
    }
    $describedByAttr = implode(' ', $describedBy);
@endphp

<div class="flex items-start">
    <div class="flex items-center h-5">
        <input type="checkbox" id="{{ $controlId }}" name="{{ $name }}" value="{{ $value }}"
            @checked($checked) @disabled($disabled) @required($required)
            aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
            @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
            class="ds-check cursor-pointer disabled:cursor-not-allowed {{ $error ? 'is-error' : '' }} {{ $class }}"
            {{ $attributes }} />
    </div>

    @if ($label)
        <div class="ml-3 text-sm">
            <label for="{{ $controlId }}" class="ds-body cursor-pointer font-medium">
                {{ $label }}
                @if ($required)
                    <span class="ds-field-required" aria-hidden="true">*</span>
                @endif
            </label>

            @if ($hint)
                <p id="{{ $controlId }}-hint" class="ds-field-hint">{{ $hint }}</p>
            @endif
        </div>
    @endif

    @if ($error)
        <p id="{{ $controlId }}-error" role="alert" aria-live="polite" aria-atomic="true"
            class="ds-field-error">{{ $error }}</p>
    @endif
</div>
