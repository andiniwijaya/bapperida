@extends('layouts.app')

@section('title', 'Detail Arsip Surat Masuk')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <x-page-header title="Detail Arsip Surat Masuk" description="Informasi lengkap arsip surat masuk.">
            <x-button :href="route('incoming-letters.index')" variant="outline">Kembali</x-button>
            <x-button id="downloadPdf" href="#" variant="success">
                <i data-lucide="download" class="h-4 w-4"></i>
                Unduh PDF Surat
            </x-button>
        </x-page-header>

        <input type="hidden" id="incoming_letter_id" value="{{ $incomingLetterId }}">
        <div id="incomingLetterDetail" class="space-y-6"></div>
    </div>

    @vite('resources/js/modules/incoming-letter/show.js')
@endsection
