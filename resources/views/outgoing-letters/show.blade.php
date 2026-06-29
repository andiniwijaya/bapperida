@extends('layouts.app')

@section('title', 'Detail Arsip Surat Keluar')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <x-page-header title="Detail Arsip Surat Keluar" description="Informasi lengkap arsip surat keluar.">
            <x-button :href="route('outgoing-letters.index')" variant="outline">Kembali</x-button>
            <x-button id="downloadPdf" href="#" variant="success">
                <i data-lucide="download" class="h-4 w-4"></i>
                Unduh PDF
            </x-button>
        </x-page-header>

        <input type="hidden" id="outgoing_letter_id" value="{{ $outgoingLetterId }}">
        <div id="outgoingLetterDetail" class="space-y-6"></div>
    </div>

    @vite('resources/js/modules/outgoing-letter/show.js')
@endsection
