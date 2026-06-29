@extends('layouts.app')

@section('title', 'Manajemen Bidang')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Manajemen Bidang" description="Kelola data bidang/departemen organisasi.">
            <x-button :href="route('admin.departments.create')">
                <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                Tambah Bidang
            </x-button>
        </x-page-header>

        <x-crud.filter-panel title="Filter Bidang">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-8">
                    <x-input id="search" name="search" label="Pencarian" placeholder="Cari kode atau nama bidang..." />
                </div>
                <div class="col-span-12 md:col-span-4">
                    <x-select id="is_active" name="is_active" label="Status" :options="['' => 'Semua', '1' => 'Aktif', '0' => 'Nonaktif']" />
                </div>
            </div>
        </x-crud.filter-panel>

        <x-crud.table-panel title="Daftar Bidang">
            <x-slot:toolbar>
                <x-table.toolbar />
            </x-slot:toolbar>

            <div class="app-data-table-wrapper">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="app-data-table__th app-data-table__th--center w-16">No</th>
                            <th class="app-data-table__th">Kode</th>
                            <th class="app-data-table__th">Nama Bidang</th>
                            <th class="app-data-table__th app-data-table__th--center">Status</th>
                            <th class="app-data-table__th app-data-table__th--center w-40">Aksi</th>
                        </tr>
                    </x-slot:head>
                    <tbody id="dataTable"></tbody>
                </x-table>
            </div>

            <div id="loadingState" class="hidden" aria-busy="false" aria-live="polite">
                <x-skeleton.table :rows="8" :columns="5" />
            </div>
            <x-empty-state id="emptyState" data-empty-page="departments" class="hidden" />
            <div id="pagination"></div>
        </x-crud.table-panel>
    </div>

    @vite('resources/js/modules/department/index.js')
@endsection
