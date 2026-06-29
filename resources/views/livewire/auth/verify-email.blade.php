<x-layouts::auth :title="'Verifikasi Email'">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="'Verifikasi Email'"
            :description="'Silakan verifikasi alamat email Anda dengan mengklik tautan yang kami kirimkan ke email Anda.'"
        />

        @if (session('status') == 'verification-link-sent')
            <x-alert type="success" title="Tautan verifikasi dikirim">
                Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
            </x-alert>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <x-button type="submit" class="w-full justify-center" x-bind:disabled="submitting">
                    <span x-show="!submitting">Kirim Ulang Email Verifikasi</span>
                    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                        <span class="ds-btn__loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                        Memproses...
                    </span>
                </x-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="submit" variant="ghost" class="w-full justify-center text-sm" data-test="logout-button">
                    Keluar
                </x-button>
            </form>
        </div>
    </div>
</x-layouts::auth>
