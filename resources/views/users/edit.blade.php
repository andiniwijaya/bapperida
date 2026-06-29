@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <x-page-header title="Ubah Pengguna" description="Perbarui data profil pengguna." />

        <input type="hidden" id="user_id" value="{{ $userId }}">

        <x-crud.form-card form-id="userForm" title="Data Pengguna" description="Perbarui informasi akun pengguna.">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12">
                    <x-input
                        id="name"
                        name="name"
                        label="Nama Lengkap"
                        placeholder="Masukkan nama lengkap..."
                        autofocus
                        required
                    />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <x-input
                        id="username"
                        name="username"
                        label="Nama Pengguna"
                        placeholder="Masukkan nama pengguna..."
                        data-validate="username"
                        required
                    />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <x-input
                        id="email"
                        name="email"
                        type="email"
                        label="Alamat Email"
                        placeholder="Masukkan alamat email..."
                        data-validate="email"
                        required
                    />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <x-select
                        id="department_id"
                        name="department_id"
                        label="Bidang"
                        placeholder="Pilih bidang..."
                        searchable
                        :options="['' => 'Pilih bidang...']"
                        required
                    />
                </div>
                <div class="col-span-12 md:col-span-6" id="roleField">
                    <x-select
                        id="role"
                        name="role"
                        label="Peran"
                        placeholder="Pilih peran..."
                        :options="['admin' => 'Admin', 'staff' => 'Staff']"
                    />
                </div>
                <div class="col-span-12 md:col-span-6" id="statusField">
                    <x-select
                        id="status"
                        name="status"
                        label="Status"
                        placeholder="Pilih status..."
                        :options="[
                            'pending' => 'Menunggu Persetujuan',
                            'active' => 'Aktif',
                            'rejected' => 'Ditolak',
                        ]"
                    />
                </div>
            </div>

            <x-slot:footer>
                <x-button :href="route('admin.users.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan Perubahan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/user/edit.js')
@endsection
