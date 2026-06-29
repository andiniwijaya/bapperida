@props([
    'controlId' => null,
    'name' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'disabled' => false,
    'error' => null,
    'required' => false,
    'multiple' => false,
    'searchable' => false,
    'clearable' => null,
    'class' => '',
    'describedByAttr' => '',
    'hasIcon' => false,
    'icon' => null,
])

@if ($searchable)
    <div
        class="ds-dropdown {{ $error ? 'is-error' : '' }} {{ $class }}"
        x-data="dsDropdown({ clearable: {{ ($clearable ?? (!$required && !$multiple)) ? 'true' : 'false' }}, placeholder: @js($placeholder ?? 'Pilih...') })"
        @click.outside="close()"
    >
        <button
            type="button"
            x-ref="trigger"
            class="ds-dropdown__trigger"
            :class="{
                'is-open': open,
                'is-disabled': disabled,
                'is-loading': loading,
                'is-placeholder': !hasSelection,
            }"
            :disabled="disabled || loading"
            :aria-expanded="open"
            aria-haspopup="listbox"
            @click="toggle()"
            @keydown="onTriggerKeydown($event)"
        >
            <span class="ds-dropdown__value" x-text="loading ? 'Memuat...' : displayLabel"></span>
            <span class="ds-dropdown__actions">
                <button
                    type="button"
                    class="ds-dropdown__clear"
                    x-show="clearable && hasSelection && !disabled && !loading"
                    x-cloak
                    @click="clearSelection($event)"
                    aria-label="Hapus pilihan"
                >
                    <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
                </button>
                <span class="ds-dropdown__chevron" aria-hidden="true">
                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                </span>
            </span>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition
            class="ds-dropdown__panel"
            role="listbox"
            @keydown.escape.stop="close(); $refs.trigger?.focus()"
        >
            <div class="ds-dropdown__search-wrap">
                <i data-lucide="search" class="ds-dropdown__search-icon h-4 w-4" aria-hidden="true"></i>
                <input
                    x-ref="searchInput"
                    type="search"
                    class="ds-dropdown__search"
                    placeholder="Cari..."
                    x-model="query"
                    @input="onSearchInput()"
                    @keydown="onSearchKeydown($event)"
                    autocomplete="off"
                />
            </div>

            <div class="ds-dropdown__options">
                <template x-if="loading">
                    <p class="ds-dropdown__empty">Memuat data...</p>
                </template>
                <template x-if="!loading && filteredOptions.length === 0">
                    <p class="ds-dropdown__empty">Tidak ada data ditemukan.</p>
                </template>
                <template x-for="(option, index) in filteredOptions" :key="`${option.value}-${index}`">
                    <button
                        type="button"
                        class="ds-dropdown__option"
                        :class="{ 'is-highlighted': isHighlighted(index), 'is-selected': option.value === selectedValue }"
                        :data-dropdown-option-index="index"
                        :disabled="option.disabled"
                        @mouseenter="highlightedIndex = index"
                        @click="selectOption(option)"
                        x-text="option.label"
                    ></button>
                </template>
            </div>
        </div>

        <select
            x-ref="nativeSelect"
            id="{{ $controlId }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            @disabled($disabled)
            @required($required)
            @if ($multiple) multiple @endif
            aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
            @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
            class="ds-dropdown__native"
            tabindex="-1"
            aria-hidden="true"
        >
            @if ($placeholder && !$multiple)
                <option value="">{{ $placeholder }}</option>
            @endif

            @forelse ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected(is_array($value) ? in_array($optionValue, $value) : $value == $optionValue)>{{ $optionLabel }}</option>
            @empty
                {{ $slot }}
            @endforelse
        </select>
    </div>
@else
    <div @class(['ds-input-wrap' => $hasIcon])>
        @if ($hasIcon)
            <span class="ds-input-icon">
                <i data-lucide="{{ $icon }}" class="h-4 w-4" aria-hidden="true"></i>
            </span>
        @endif

        <select
            id="{{ $controlId }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            @disabled($disabled)
            @required($required)
            @if ($multiple) multiple @endif
            aria-invalid="{{ filled($error) ? 'true' : 'false' }}"
            @if ($describedByAttr) aria-describedby="{{ $describedByAttr }}" @endif
            class="ds-select {{ $hasIcon ? 'ds-select--leading-icon ' : '' }}{{ $error ? 'is-error' : '' }} {{ $class }}"
            {{ $attributes }}
        >
            @if ($placeholder && !$multiple)
                <option value="">{{ $placeholder }}</option>
            @endif

            @forelse ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected(is_array($value) ? in_array($optionValue, $value) : $value == $optionValue)>{{ $optionLabel }}</option>
            @empty
                {{ $slot }}
            @endforelse
        </select>
    </div>
@endif
