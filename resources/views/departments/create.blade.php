@extends('layouts.app')

@section('title', 'Tambah Bidang')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <x-page-header title="Tambah Bidang" description="Tambahkan bidang baru ke sistem." />

        <x-crud.form-card form-id="departmentForm" title="Data Bidang" description="Lengkapi kode dan nama bidang.">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 md:col-span-4">
                    <x-input
                        id="code"
                        name="code"
                        label="Kode Bidang"
                        placeholder="Masukkan kode bidang..."
                        autofocus
                        required
                    />
                </div>
                <div class="col-span-12 md:col-span-8">
                    <x-input
                        id="name"
                        name="name"
                        label="Nama Bidang"
                        placeholder="Masukkan nama bidang..."
                        required
                    />
                </div>
            </div>

            <x-slot:footer>
                <x-button :href="route('admin.departments.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/department/create.js')
@endsection
