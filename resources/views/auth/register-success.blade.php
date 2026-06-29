<x-layouts::auth :title="'Registrasi Berhasil'">
    <div class="flex flex-col gap-6 text-center">
        <x-auth-header
            :title="'Registrasi Berhasil'"
            :description="'Terima kasih telah mendaftar di Sistem Registrasi Penomoran dan Arsip Surat BAPPERIDA Kabupaten Bandung.'"
        />

        <x-alert type="success">
            {{ session('status') ?? 'Silakan periksa email Anda untuk verifikasi akun. Setelah email terverifikasi, akun akan menunggu persetujuan Super Admin sebelum dapat digunakan.' }}
        </x-alert>

        <p class="text-sm text-charcoal-600 dark:text-slate-400">
            Setelah disetujui, Anda dapat masuk menggunakan email atau username beserta kata sandi yang telah didaftarkan.
        </p>

        <x-button href="{{ route('login') }}" class="w-full justify-center">
            Ke Halaman Login
        </x-button>
    </div>
</x-layouts::auth>
