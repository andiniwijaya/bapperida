<x-errors.layout
    :title="'Anda tidak memiliki hak akses untuk membuka halaman ini.'"
    :code="'403'"
    icon="shield-alert"
>
    <x-slot:actions>
        <x-button type="button" variant="outline" onclick="window.history.back()">Kembali</x-button>
        <x-button href="{{ route('home') }}" variant="primary">Beranda</x-button>
    </x-slot:actions>
</x-errors.layout>
