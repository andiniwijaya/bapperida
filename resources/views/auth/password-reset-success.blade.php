<x-layouts::auth :title="'Kata Sandi Berhasil Dibuat'">
    <div class="flex flex-col gap-6 text-center">
        <x-auth-header
            :title="'Kata Sandi Berhasil Dibuat'"
            :description="'Kata sandi Anda telah berhasil dibuat.'"
        />

        <x-alert type="success">
            {{ session('status') ?? 'Kata sandi berhasil dibuat. Silakan masuk menggunakan alamat email dan kata sandi yang baru Anda buat.' }}
        </x-alert>

        <p class="text-sm text-charcoal-600 dark:text-slate-400">
            Gunakan email atau username beserta kata sandi baru Anda untuk masuk ke sistem.
        </p>

        <x-button href="{{ route('login') }}" class="w-full justify-center">
            Masuk
        </x-button>
    </div>
</x-layouts::auth>
