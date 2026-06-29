@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'disabled' => false,
    'error' => null,
    'required' => false,
    'hint' => null,
    'label' => null,
    'multiple' => false,
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

    <select id="{{ $controlId }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" @disabled($disabled)
        @required($required) @if ($multiple) multiple @endif
        aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
        @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
        class="ds-select {{ $error ? 'is-error' : '' }} {{ $class }}"
        {{ $attributes }}>
        @if ($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @forelse ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(is_array($value) ? in_array($optionValue, $value) : $value == $optionValue)>
                {{ $optionLabel }}
            </option>
        @empty
            {{ $slot }}
        @endforelse
    </select>

    @if ($error)
        <p id="{{ $controlId }}-error" role="alert" aria-live="polite" aria-atomic="true"
            class="ds-field-error">{{ $error }}</p>
    @endif

    @if ($hint)
        <p id="{{ $controlId }}-hint" class="ds-field-hint">{{ $hint }}</p>
    @endif
</div>
