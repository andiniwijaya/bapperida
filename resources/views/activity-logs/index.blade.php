@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Log Aktivitas" description="Audit trail aktivitas pengguna dalam sistem." />

        <x-crud.filter-panel title="Filter Aktivitas">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-4">
                    <x-input id="search" name="search" label="Pencarian" placeholder="Cari deskripsi, modul, aksi..." />
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <x-input id="module" name="module" label="Modul" />
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <x-input id="action" name="action" label="Aksi" />
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <x-input id="period_start" name="period_start" type="date" label="Dari Tanggal" />
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <x-input id="period_end" name="period_end" type="date" label="Sampai Tanggal" />
                </div>
            </div>
        </x-crud.filter-panel>

        <x-crud.action-bar>
            <x-button id="export-excel" type="button" variant="success">
                <i data-lucide="file-spreadsheet" class="h-4 w-4" aria-hidden="true"></i>
                Ekspor Excel
            </x-button>
        </x-crud.action-bar>

        <x-crud.table-panel title="Daftar Aktivitas">
            <x-slot:toolbar>
                <x-table.toolbar />
            </x-slot:toolbar>

            <div class="app-data-table-wrapper">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="app-data-table__th app-data-table__th--center w-16">No</th>
                            <th class="app-data-table__th">Waktu</th>
                            <th class="app-data-table__th">User</th>
                            <th class="app-data-table__th">Modul</th>
                            <th class="app-data-table__th">Aksi</th>
                            <th class="app-data-table__th">Deskripsi</th>
                            <th class="app-data-table__th app-data-table__th--center w-24">Detail</th>
                        </tr>
                    </x-slot:head>
                    <tbody id="dataTable"></tbody>
                </x-table>
            </div>

            <div id="loadingState" class="hidden" aria-busy="false" aria-live="polite">
                <x-skeleton.table :rows="8" :columns="6" />
            </div>
            <x-empty-state id="emptyState" data-empty-page="activity-logs" class="hidden" />
            <div id="pagination"></div>
        </x-crud.table-panel>
    </div>

    @vite('resources/js/modules/activity-log/index.js')
@endsection
