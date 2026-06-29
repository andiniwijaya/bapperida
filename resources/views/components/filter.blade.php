@props([
    'label' => 'Filter',
    'name' => 'filter',
    'value' => null,
    'options' => [],
    'disabled' => false,
    'class' => '',
])

<div class="flex flex-col gap-1">
    @if ($label)
        <label for="{{ $name }}" class="ds-label mb-0">{{ $label }}</label>
    @endif

    <select id="{{ $name }}" name="{{ $name }}" @disabled($disabled)
        class="ds-select {{ $class }}"
        {{ $attributes }}>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($value == $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
