@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="space-y-6">
        <x-page-header
            title="{{ auth()->user()->role === 'admin' ? 'Manajemen Staff' : 'Manajemen Pengguna' }}"
            description="{{ auth()->user()->role === 'admin' ? 'Kelola akun staff BAPPERIDA.' : 'Kelola akun admin dan staff BAPPERIDA.' }}"
        >
            @can('create', App\Models\User::class)
                <x-button :href="route('admin.users.create')">
                    <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                    {{ auth()->user()->role === 'admin' ? 'Tambah Staff' : 'Tambah Pengguna' }}
                </x-button>
            @endcan
        </x-page-header>

        <x-crud.filter-panel title="{{ auth()->user()->role === 'admin' ? 'Filter Staff' : 'Filter Pengguna' }}">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-8">
                    <x-input id="search" name="search" label="Pencarian"
                        placeholder="Cari nama, username, atau email..." />
                </div>
            </div>
        </x-crud.filter-panel>

        <x-crud.table-panel title="{{ auth()->user()->role === 'admin' ? 'Daftar Staff' : 'Daftar Pengguna' }}">
            <x-slot:toolbar>
                <x-table.toolbar />
            </x-slot:toolbar>

            <div class="app-data-table-wrapper">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="app-data-table__th app-data-table__th--center w-16">No</th>
                            <th class="app-data-table__th">Nama</th>
                            <th class="app-data-table__th">Username</th>
                            <th class="app-data-table__th">Email</th>
                            <th class="app-data-table__th">Role</th>
                            <th class="app-data-table__th">Bidang</th>
                            <th class="app-data-table__th app-data-table__th--center">Status Akun</th>
                            <th class="app-data-table__th app-data-table__th--center">Status Password</th>
                            <th class="app-data-table__th app-data-table__th--center w-40">Aksi</th>
                        </tr>
                    </x-slot:head>
                    <tbody id="dataTable"></tbody>
                </x-table>
            </div>

            <div id="loadingState" class="hidden" aria-busy="false" aria-live="polite">
                <x-skeleton.table :rows="8" :columns="6" />
            </div>
            <x-empty-state id="emptyState" data-empty-page="users" class="hidden" />
            <div id="pagination"></div>
        </x-crud.table-panel>
    </div>

    @vite('resources/js/modules/user/index.js')
@endsection
