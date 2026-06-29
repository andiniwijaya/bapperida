@extends('layouts.app')

@section('title', 'Edit Bidang')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <x-page-header title="Edit Bidang" description="Perbarui data bidang organisasi." />

        <input type="hidden" id="department_id" value="{{ $departmentId }}">

        <x-crud.form-card form-id="departmentForm" title="Data Bidang" description="Perbarui kode dan nama bidang.">
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
                <div class="col-span-12">
                    <x-checkbox id="is_active" name="is_active" label="Bidang aktif" value="1" />
                </div>
            </div>

            <x-slot:footer>
                <x-button :href="route('admin.departments.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan Perubahan</x-button>
            </x-slot:footer>
        </x-crud.form-card>
    </div>

    @vite('resources/js/modules/department/edit.js')
@endsection
