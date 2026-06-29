<x-errors.layout
    :title="'Sistem sedang dalam proses pemeliharaan.'"
    :code="'503'"
    :description="'Silakan kembali beberapa saat lagi.'"
    icon="construction"
>
    <x-slot:actions>
        <x-button href="{{ route('home') }}" variant="primary">Beranda</x-button>
    </x-slot:actions>
</x-errors.layout>
