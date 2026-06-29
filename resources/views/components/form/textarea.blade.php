@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'required' => false,
    'hint' => null,
    'label' => null,
    'rows' => 4,
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

<div class="w-full">
    @if ($label)
        <label for="{{ $controlId }}" class="ds-label">
            {{ $label }}
            @if ($required)
                <span class="ds-field-required" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $controlId }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        @disabled($disabled) @readonly($readonly) @required($required)
        aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
        @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
        class="ds-textarea {{ $error ? 'is-error' : '' }} {{ $class }}"
        {{ $attributes }}>{{ $value }}</textarea>

    @if ($error)
        <p id="{{ $controlId }}-error" role="alert" aria-live="polite" aria-atomic="true"
            class="ds-field-error">{{ $error }}</p>
    @endif

    @if ($hint)
        <p id="{{ $controlId }}-hint" class="ds-field-hint">{{ $hint }}</p>
    @endif
</div>
