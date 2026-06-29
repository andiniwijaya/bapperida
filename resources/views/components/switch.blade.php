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

<x-form.switch :id="$id" :name="$name" :checked="$checked" :disabled="$disabled" :error="$error"
    :label="$label" :hint="$hint" :class="$class" {{ $attributes }} />
