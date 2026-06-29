@extends('layouts.app')

@section('title', 'Arsip Surat Masuk')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Arsip Surat Masuk" description="Kelola arsip surat masuk BAPPERIDA Kabupaten Bandung.">
            <x-button :href="route('incoming-letters.create')">
                <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                Tambah Surat Masuk
            </x-button>
        </x-page-header>

        <x-crud.filter-panel title="Filter Surat Masuk">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 xl:col-span-4">
                    <x-input id="search" name="search" label="Pencarian"
                        placeholder="Cari nomor surat, perihal, pengirim..." />
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-select id="year" name="year" label="Tahun" :options="['' => 'Semua']"></x-select>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-select id="department" name="department" label="Bidang" searchable :options="['' => 'Semua']"></x-select>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-select id="letter_attribute" name="letter_attribute" label="Jenis Surat"
                        :options="['' => 'Semua']"></x-select>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-select id="status" name="status" label="Status" :options="['' => 'Semua']"></x-select>
                </div>
            </div>
        </x-crud.filter-panel>

        <x-crud.action-bar>
            <x-button id="print-selected" type="button" variant="success">
                <i data-lucide="printer" class="h-4 w-4" aria-hidden="true"></i>
                Cetak
            </x-button>
            <x-button id="export-pdf" type="button" variant="success">
                <i data-lucide="file-text" class="h-4 w-4" aria-hidden="true"></i>
                Ekspor PDF
            </x-button>
            <x-button id="export-excel" type="button" variant="success">
                <i data-lucide="file-spreadsheet" class="h-4 w-4" aria-hidden="true"></i>
                Ekspor Excel
            </x-button>
        </x-crud.action-bar>

        <x-crud.table-panel title="Daftar Surat Masuk">
            <x-slot:toolbar>
                <x-table.toolbar>
                    <x-slot:extra>
                        <p class="text-sm text-charcoal-600 dark:text-slate-400">Dipilih: <span id="selectedCount">0</span></p>
                    </x-slot:extra>
                </x-table.toolbar>
            </x-slot:toolbar>

            <div class="app-data-table-wrapper">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="app-data-table__th app-data-table__th--center w-12">
                                <input id="select-all" type="checkbox" class="rounded border-ocean-900/40 bg-transparent">
                            </th>
                            <th class="app-data-table__th app-data-table__th--center w-16">No</th>
                            <th class="app-data-table__th">Nomor Surat</th>
                            <th class="app-data-table__th">Tanggal Surat</th>
                            <th class="app-data-table__th">Tanggal Diterima</th>
                            <th class="app-data-table__th">Tanggal Disposisi</th>
                            <th class="app-data-table__th">Pengirim</th>
                            <th class="app-data-table__th">Bidang</th>
                            <th class="app-data-table__th">Bidang Disposisi</th>
                            <th class="app-data-table__th">Perihal</th>
                            <th class="app-data-table__th">Nama Agenda</th>
                            <th class="app-data-table__th">Isi Ringkas</th>
                            <th class="app-data-table__th">Jenis Surat</th>
                            <th class="app-data-table__th">Lampiran</th>
                            <th class="app-data-table__th app-data-table__th--center">Status</th>
                            <th class="app-data-table__th app-data-table__th--center w-40">Aksi</th>
                        </tr>
                    </x-slot:head>
                    <tbody id="incomingLetterTable"></tbody>
                </x-table>
            </div>

            <div id="loadingState" class="hidden" aria-busy="false" aria-live="polite">
                <x-skeleton.table :rows="8" :columns="8" />
            </div>
            <x-empty-state id="emptyState" data-empty-page="incoming-letters" class="hidden" />
            <div id="pagination"></div>
        </x-crud.table-panel>
    </div>

    @vite('resources/js/modules/incoming-letter/index.js')
@endsection
