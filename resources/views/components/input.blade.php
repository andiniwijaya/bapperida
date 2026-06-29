@props([
    'id' => null,
    'type' => 'text',
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'required' => false,
    'hint' => null,
    'tooltip' => null,
    'label' => null,
    'icon' => null,
    'class' => '',
    'viewable' => false,
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
    $isPassword = $type === 'password' || ($viewable && $type !== 'text');
    $hasIcon = filled($icon);
    $inputClass =
        'ds-input ' .
        ($hasIcon ? 'ds-input--leading-icon ' : '') .
        ($isPassword ? 'ds-input--trailing-action ' : '') .
        ($error ? 'is-error ' : '') .
        $class;
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

    @if ($isPassword)
        <div x-data="{ show: false }" class="ds-input-wrap">
            @if ($hasIcon)
                <span class="ds-input-icon">
                    <i data-lucide="{{ $icon }}" class="h-4 w-4" aria-hidden="true"></i>
                </span>
            @endif
            <input
                :type="show ? 'text' : 'password'"
                id="{{ $controlId }}"
                name="{{ $name }}"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
                @disabled($disabled)
                @readonly($readonly)
                @required($required)
                aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
                @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
                class="{{ $inputClass }}"
                {{ $attributes }}
            />
            <button
                type="button"
                @click="show = !show"
                class="ds-input-action"
                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
            >
                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        </div>
    @else
        <div @class(['ds-input-wrap' => $hasIcon])>
            @if ($hasIcon)
                <span class="ds-input-icon">
                    <i data-lucide="{{ $icon }}" class="h-4 w-4" aria-hidden="true"></i>
                </span>
            @endif
            <input
            type="{{ $type }}"
            id="{{ $controlId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            @readonly($readonly)
            @required($required)
            aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
            @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
            class="{{ $inputClass }}"
            {{ $attributes }}
        />
        </div>
    @endif

    @if ($error)
        <p id="{{ $controlId }}-error" role="alert" aria-live="polite" aria-atomic="true"
            class="ds-field-error">{{ $error }}</p>
    @endif

    @if ($hint)
        <p id="{{ $controlId }}-hint" class="ds-field-hint">{{ $hint }}</p>
    @endif
</div>
