@props([
    'title' => 'Apakah Anda yakin?',
    'message' => null,
    'confirmText' => 'Konfirmasi',
    'cancelText' => 'Batal',
    'confirmVariant' => 'danger', // primary, danger
    'open' => false,
    'class' => '',
])

<div x-show="@if ($open) true @else {{ $attributes->get('x-show') ?? 'false' }} @endif"
    x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" {{ $attributes }}>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70"></div>

    <!-- Dialog -->
    <div class="relative max-w-sm w-full bg-white dark:bg-navy-800 rounded-xl shadow-xl border border-charcoal-100 dark:border-navy-700 {{ $class }}">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-maroon-100 dark:bg-maroon-900/30 rounded-full">
                    <svg class="w-6 h-6 text-maroon-600 dark:text-maroon-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4v2m0-10a8 8 0 100 16 8 8 0 000-16z" />
                    </svg>
                </div>

                <h2 class="text-lg font-bold text-charcoal-900 dark:text-slate-100">{{ $title }}</h2>
            </div>

            @if ($message)
                <p class="text-charcoal-600 dark:text-slate-400 mb-6">{{ $message }}</p>
            @else
                <div class="mb-6 text-charcoal-600 dark:text-slate-400">
                    {{ $slot }}
                </div>
            @endif

            <div class="flex gap-3">
                <button
                    @click="@if ($attributes->get('x-on:cancel')) {{ $attributes->get('x-on:cancel') }} @else $dispatch('close-dialog') @endif"
                    class="ds-btn ds-btn--secondary flex-1">
                    {{ $cancelText }}
                </button>

                <button
                    @click="@if ($attributes->get('x-on:confirm')) {{ $attributes->get('x-on:confirm') }} @else $dispatch('confirm-action') @endif"
                    class="ds-btn flex-1 {{ $confirmVariant === 'danger' ? 'ds-btn--danger' : 'ds-btn--primary' }}">
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
