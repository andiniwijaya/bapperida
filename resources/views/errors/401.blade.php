<x-errors.layout
    :title="'Anda belum masuk ke dalam sistem.'"
    :code="'401'"
    :description="'Silakan masuk terlebih dahulu untuk mengakses halaman ini.'"
    icon="log-in"
>
    <x-slot:actions>
        <x-button href="{{ route('login') }}" variant="primary">Masuk</x-button>
        <x-button type="button" variant="outline" onclick="window.history.back()">Kembali</x-button>
    </x-slot:actions>
</x-errors.layout>
