<x-errors.layout
    :title="'Sesi Anda telah berakhir.'"
    :code="'419'"
    :description="'Silakan masuk kembali.'"
    icon="clock-alert"
>
    <x-slot:actions>
        <x-button href="{{ route('login') }}" variant="primary">Masuk</x-button>
    </x-slot:actions>
</x-errors.layout>
