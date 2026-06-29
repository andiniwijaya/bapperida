@extends('layouts.app')

@section('title', 'Ubah Arsip Surat Keluar')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Ubah Arsip Surat Keluar</h1>
                <p class="ds-subheading">Perbarui arsip surat keluar yang telah tersimpan.</p>
            </div>
            <a href="{{ route('outgoing-letters.index') }}" class="app-crud-back-link">Kembali</a>
        </div>

        <input type="hidden" id="outgoing_letter_id" value="{{ $outgoingLetterId }}">

        <x-crud.form-card
            form-id="outgoingLetterForm"
            title="Data Surat Keluar"
            description="Perbarui jenis surat, status, dan lampiran."
            enctype="multipart/form-data"
        >
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 lg:col-span-6">
                    <x-select id="letter_type" name="letter_type" label="Jenis Surat" placeholder="Pilih jenis surat..." required />
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <x-select id="status" name="status" label="Status" placeholder="Pilih status..." required />
                </div>
                <div class="col-span-12">
                    <p class="ds-label mb-3">Informasi Registrasi</p>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="app-crud-info-tile">
                            <p class="app-crud-info-tile__label">Nomor Surat</p>
                            <p id="registration_letter_number" class="app-crud-info-tile__value">-</p>
                        </div>
                        <div class="app-crud-info-tile">
                            <p class="app-crud-info-tile__label">Kode Indeks</p>
                            <p id="registration_index_code" class="app-crud-info-tile__value">-</p>
                        </div>
                        <div class="app-crud-info-tile">
                            <p class="app-crud-info-tile__label">Kode Surat</p>
                            <p id="registration_letter_code" class="app-crud-info-tile__value">-</p>
                        </div>
                        <div class="app-crud-info-tile">
                            <p class="app-crud-info-tile__label">Bidang</p>
                            <p id="registration_department" class="app-crud-info-tile__value">-</p>
                        </div>
                        <div class="app-crud-info-tile">
                            <p class="app-crud-info-tile__label">Perihal</p>
                            <p id="registration_subject" class="app-crud-info-tile__value">-</p>
                        </div>
                        <div class="app-crud-info-tile">
                            <p class="app-crud-info-tile__label">Tujuan</p>
                            <p id="registration_recipient" class="app-crud-info-tile__value">-</p>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <x-input id="attachment" name="attachment" label="Lampiran" placeholder="Masukkan keterangan lampiran..." />
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <x-form.file-upload
                        id="file"
                        name="file"
                        label="Unggah File PDF"
                        accept="application/pdf,.pdf"
                        hint="Format PDF. Ukuran maksimal 10 MB."
                        max-size="10 MB"
                    />
                </div>
                <div class="col-span-12">
                    <x-textarea id="notes" name="notes" label="Catatan" placeholder="Masukkan catatan tambahan..." rows="4" />
                </div>
            </div>

            <x-slot:footer>
                <x-button :href="route('outgoing-letters.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan Perubahan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/outgoing-letter/edit.js')
@endsection
