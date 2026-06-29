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
    'tooltip' => null,
    'label' => null,
    'icon' => null,
    'multiple' => false,
    'searchable' => false,
    'clearable' => null,
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
    $hasIcon = filled($icon) && !$searchable;
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $controlId }}" class="ds-label">
            <span class="inline-flex items-center gap-1.5">
                {{ $label }}
                @if ($required)
                    <span class="ds-field-required" aria-hidden="true">*</span>
                @endif
                @if ($tooltip)
                    <button
                        type="button"
                        class="inline-flex text-ocean-600 hover:text-ocean-700 dark:text-gold-400 dark:hover:text-gold-300"
                        data-app-tooltip="{{ $tooltip }}"
                        aria-label="Informasi {{ $label }}"
                    >
                        <i data-lucide="help-circle" class="h-4 w-4" aria-hidden="true"></i>
                    </button>
                @endif
            </span>
        </label>
    @endif

    @include('components.select-control', [
        'controlId' => $controlId,
        'name' => $name,
        'value' => $value,
        'options' => $options,
        'placeholder' => $placeholder,
        'disabled' => $disabled,
        'error' => $error,
        'required' => $required,
        'multiple' => $multiple,
        'searchable' => $searchable,
        'clearable' => $clearable,
        'class' => $class,
        'describedByAttr' => $describedByAttr,
        'hasIcon' => $hasIcon,
        'icon' => $icon,
    ])

    @if ($error)
        <p id="{{ $controlId }}-error" role="alert" aria-live="polite" aria-atomic="true"
            class="ds-field-error">{{ $error }}</p>
    @endif

    @if ($hint)
        <p id="{{ $controlId }}-hint" class="ds-field-hint">{{ $hint }}</p>
    @endif
</div>
