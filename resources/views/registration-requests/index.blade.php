@extends('layouts.app')

@section('title', 'Persetujuan Registrasi')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Persetujuan Registrasi" description="Tinjau dan proses permintaan registrasi akun baru." />

        <x-crud.table-panel title="Daftar Persetujuan Registrasi">
            <x-slot:toolbar>
                <x-table.toolbar />
            </x-slot:toolbar>

            <div class="app-data-table-wrapper">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="app-data-table__th app-data-table__th--center w-16">No</th>
                            <th class="app-data-table__th">Nama</th>
                            <th class="app-data-table__th">Email</th>
                            <th class="app-data-table__th">Nama Pengguna</th>
                            <th class="app-data-table__th app-data-table__th--center">Status</th>
                            <th class="app-data-table__th">Tanggal Daftar</th>
                            <th class="app-data-table__th app-data-table__th--center w-48">Aksi</th>
                        </tr>
                    </x-slot:head>
                    <tbody id="dataTable"></tbody>
                </x-table>
            </div>

            <div id="loadingState" class="hidden" aria-busy="false" aria-live="polite">
                <x-skeleton.table :rows="8" :columns="6" />
            </div>
            <x-empty-state id="emptyState" data-empty-page="registration-requests" class="hidden" />
            <div id="pagination"></div>
        </x-crud.table-panel>
    </div>

    @vite('resources/js/modules/registration-request/index.js')
@endsection
