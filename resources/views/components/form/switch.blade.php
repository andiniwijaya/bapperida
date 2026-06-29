@props([
    'id' => null,
    'name' => null,
    'checked' => false,
    'disabled' => false,
    'error' => null,
    'label' => null,
    'hint' => null,
    'class' => '',
])

<div class="flex items-center justify-between gap-4" x-data="{ checked: {{ $checked ? 'true' : 'false' }} }">
    <div class="flex flex-col">
        @if ($label)
            <label for="{{ $id ?? $name }}" class="ds-label mb-0">{{ $label }}</label>
        @endif

        @if ($hint)
            <p class="ds-field-hint mt-1">{{ $hint }}</p>
        @endif
    </div>

    <button type="button" role="switch" aria-checked="{{ $checked ? 'true' : 'false' }}"
        id="{{ $id ?? $name }}" @disabled($disabled) @click="checked = !checked"
        :class="checked ? 'ds-switch is-on' : 'ds-switch'"
        class="disabled:cursor-not-allowed {{ $error ? 'is-error' : '' }} {{ $class }}"
        {{ $attributes }}>
        <span class="ds-switch__thumb"></span>
    </button>

    <input type="hidden" name="{{ $name }}" :value="checked ? 1 : 0" />

    @if ($error)
        <p class="ds-field-error mt-1">{{ $error }}</p>
    @endif
</div>
