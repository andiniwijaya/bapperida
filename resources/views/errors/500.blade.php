<x-errors.layout
    :title="'Terjadi kesalahan pada sistem.'"
    :code="'500'"
    :description="'Silakan coba beberapa saat lagi.'"
    icon="server-crash"
>
    <x-slot:actions>
        <x-button href="{{ route('home') }}" variant="primary">Beranda</x-button>
        <x-button type="button" variant="outline" onclick="window.history.back()">Kembali</x-button>
    </x-slot:actions>
</x-errors.layout>
