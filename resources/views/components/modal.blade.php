@props([
    'open' => false,
    'title' => null,
    'maxWidth' => 'max-w-md', // max-w-sm, max-w-md, max-w-lg, max-w-xl
    'closeButton' => true,
    'backdrop' => true,
    'class' => '',
])

<div x-show="@if ($open) true @else {{ $attributes->get('x-show') ?? 'false' }} @endif"
    x-transition
    data-form-modal
    @keydown.escape.window="$dispatch('close-modal')"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 {{ !$backdrop ? 'pointer-events-none' : '' }}"
    @if ($backdrop) @click="@if ($attributes->get('x-on:click.self')) $event.target === $el && ({{ $attributes->get('x-on:click.self') }}) @endif"
    @endif
    {{ $attributes }}
    >
    <!-- Backdrop -->
    @if ($backdrop)
        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="$dispatch('close-modal')"></div>
    @endif

    <!-- Modal -->
    <div
        class="relative {{ $maxWidth }} w-full max-h-[min(90dvh,90vh)] flex flex-col overflow-hidden bg-white dark:bg-navy-800 rounded-lg shadow-xl {{ $class }}"
        x-init="$nextTick(() => { const el = $el.querySelector('[autofocus], [data-autofocus], input:not([type=hidden]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled])'); el?.focus(); })"
    >
        <!-- Header -->
        @if ($title || $closeButton)
            <div class="flex shrink-0 items-center justify-between px-4 py-4 border-b border-charcoal-100 dark:border-navy-700 sm:px-6">
                @if ($title)
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h2>
                @endif

                @if ($closeButton)
                    <button
                        @click="@if ($attributes->get('x-on:click')) {{ $attributes->get('x-on:click') }} @else $dispatch('close-modal') @endif"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        <!-- Content -->
        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6" data-modal-content>
            <div class="ds-modal-inline-loading hidden" data-modal-loading aria-busy="false" aria-live="polite">
                <x-loading />
            </div>
            {{ $slot }}
        </div>

        <!-- Footer (optional) -->
        @if ($attributes->has('footer'))
            <div class="shrink-0 px-4 py-4 border-t border-charcoal-100 dark:border-navy-700 flex flex-wrap justify-end gap-3 sm:px-6">
                {{ $attributes->get('footer') }}
            </div>
        @endif
    </div>
</div>
