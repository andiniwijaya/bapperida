<x-errors.layout
    :title="'Terlalu banyak permintaan.'"
    :code="'429'"
    :description="'Silakan tunggu beberapa saat sebelum mencoba lagi.'"
    icon="timer"
>
    <x-slot:actions>
        <x-button type="button" variant="primary" onclick="window.history.back()">Kembali</x-button>
    </x-slot:actions>
</x-errors.layout>
