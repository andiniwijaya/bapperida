<x-layouts::auth :title="'Menunggu Persetujuan'">
    <div class="flex flex-col gap-6 text-center">
        <x-auth-header
            :title="'Akun Menunggu Persetujuan'"
            :description="'Registrasi Anda telah diterima dan sedang dalam proses peninjauan oleh Super Admin.'"
        />

        <x-empty-state
            title="Status: Menunggu Persetujuan"
            description="Akun Anda masih menunggu persetujuan Super Admin. Anda akan dapat masuk setelah akun disetujui dan diaktifkan."
        />

        <x-alert type="info">
            Jika Anda belum memverifikasi email, periksa kotak masuk email Anda dan ikuti tautan verifikasi terlebih dahulu.
        </x-alert>

        <x-button href="{{ route('login') }}" variant="outline" class="w-full justify-center">
            Kembali ke Halaman Masuk
        </x-button>
    </div>
</x-layouts::auth>
