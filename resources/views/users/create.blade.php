@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    @php
        $actorRole = auth()->user()->role;
        $presetRole = request('role');
        $roleOptions = $actorRole === 'admin'
            ? ['staff' => 'Staff']
            : ['admin' => 'Admin', 'staff' => 'Staff'];
        $defaultRole = $actorRole === 'admin'
            ? 'staff'
            : ($presetRole === 'admin' || $presetRole === 'staff' ? $presetRole : 'staff');
    @endphp

    <div class="mx-auto max-w-3xl space-y-6">
        <x-page-header
            title="{{ $actorRole === 'admin' ? 'Tambah Staff' : 'Tambah Pengguna' }}"
            description="{{ $actorRole === 'admin' ? 'Buat akun staff baru.' : 'Buat akun admin atau staff baru.' }}"
        />

        <input type="hidden" id="actorRole" value="{{ $actorRole }}">

        <x-crud.form-card
            form-id="userForm"
            title="Data Pengguna"
            description="Lengkapi informasi akun pengguna baru."
        >
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
                @if ($actorRole === 'admin')
                    <input type="hidden" id="role" name="role" value="staff" />
                @else
                    <div class="col-span-12 md:col-span-6">
                        <x-select
                            id="role"
                            name="role"
                            label="Peran"
                            placeholder="Pilih peran..."
                            :options="$roleOptions"
                            :value="$defaultRole"
                            required
                        />
                    </div>
                @endif
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
            </div>

            <x-slot:footer>
                <x-button :href="route('admin.users.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/user/create.js')
@endsection
