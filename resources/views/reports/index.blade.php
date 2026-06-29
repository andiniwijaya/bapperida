@extends('layouts.app')

@section('title', 'Laporan Surat')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Laporan Surat"
            description="Lihat dan cetak laporan arsip surat berdasarkan jenis dan filter." />

        <x-crud.filter-panel title="Filter Laporan">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 xl:col-span-3">
                    <x-select id="reportType" name="reportType" label="Jenis Laporan" :options="[
                        'all' => 'Semua',
                        'registration' => 'Registrasi Penomoran',
                        'incoming' => 'Arsip Surat Masuk',
                        'outgoing' => 'Arsip Surat Keluar',
                    ]" />
                </div>
                <div class="col-span-12 xl:col-span-3">
                    <x-input id="search" name="search" label="Pencarian"
                        placeholder="Cari nomor surat, perihal, bidang..." />
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-select id="year" name="year" label="Tahun" :options="['' => 'Semua']" />
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-select id="department" name="department" label="Bidang" searchable :options="['' => 'Semua']" />
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-input id="periodStart" name="periodStart" label="Periode Mulai" type="date" />
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-2">
                    <x-input id="periodEnd" name="periodEnd" label="Periode Akhir" type="date" />
                </div>
            </div>
        </x-crud.filter-panel>

        <x-crud.action-bar>
            <x-button id="print-page" type="button" variant="success">
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

        <x-crud.table-panel title="Daftar Laporan">
            <x-slot:toolbar>
                <x-table.toolbar />
            </x-slot:toolbar>

            <div class="app-data-table-wrapper">
                <table id="reportTable" class="app-data-table min-w-full">
                    <thead id="reportTableHead"></thead>
                    <tbody id="reportTableBody"></tbody>
                </table>
            </div>

            <div id="loadingState" class="hidden" aria-busy="false" aria-live="polite">
                <x-skeleton.table :rows="8" :columns="6" />
            </div>
            <x-empty-state id="emptyState" data-empty-page="reports" class="hidden" />
            <div id="pagination"></div>
        </x-crud.table-panel>
    </div>

    @vite('resources/js/modules/report/index.js')
@endsection
