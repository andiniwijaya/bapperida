<x-errors.layout
    :title="'Halaman yang Anda cari tidak ditemukan.'"
    :code="'404'"
    icon="file-question"
>
    <x-slot:actions>
        <x-button href="{{ route('home') }}" variant="primary">Beranda</x-button>
        <x-button type="button" variant="outline" onclick="window.history.back()">Kembali</x-button>
    </x-slot:actions>
</x-errors.layout>
