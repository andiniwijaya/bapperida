@extends('layouts.app')

@section('title', 'Ubah Arsip Surat Masuk')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Ubah Arsip Surat Masuk</h1>
                <p class="ds-subheading">Perbarui arsip surat masuk BAPPERIDA Kabupaten Bandung.</p>
            </div>
            <a href="{{ route('incoming-letters.index') }}" class="app-crud-back-link">Kembali</a>
        </div>

        <input type="hidden" id="incoming_letter_id" value="{{ $incomingLetterId }}">

        <x-crud.form-card
            form-id="incomingLetterForm"
            title="Data Surat Masuk"
            description="Perbarui informasi arsip surat masuk."
            enctype="multipart/form-data"
        >
            @include('incoming-letters.partials.form-fields')

            <x-slot:footer>
                <x-button :href="route('incoming-letters.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan Perubahan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/incoming-letter/edit.js')
@endsection
