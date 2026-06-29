@extends('layouts.app')

@section('title', 'Tambah Arsip Surat Masuk')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Tambah Arsip Surat Masuk</h1>
                <p class="ds-subheading">Tambahkan arsip surat masuk BAPPERIDA Kabupaten Bandung.</p>
            </div>
            <a href="{{ route('incoming-letters.index') }}" class="app-crud-back-link">Kembali</a>
        </div>

        <x-crud.form-card
            form-id="incomingLetterForm"
            title="Data Surat Masuk"
            description="Lengkapi informasi arsip surat masuk."
            enctype="multipart/form-data"
        >
            @include('incoming-letters.partials.form-fields', ['autofocus' => true])

            <x-slot:footer>
                <x-button :href="route('incoming-letters.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/incoming-letter/create.js')
@endsection
