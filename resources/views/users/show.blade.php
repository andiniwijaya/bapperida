@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header title="Detail Pengguna" description="Informasi lengkap akun pengguna." />

        <input type="hidden" id="user_id" value="{{ $userId }}">
        <div id="userDetail" class="space-y-6"></div>

        <div class="flex flex-wrap gap-3">
            <x-button :href="route('admin.users.index')" variant="secondary">Kembali</x-button>
            <a id="editLink" href="#" class="hidden inline-flex items-center gap-2 rounded-lg bg-ocean-800 px-4 py-2 text-sm font-medium text-white hover:bg-ocean-900">Ubah</a>
            <button id="resendPasswordSetupBtn" type="button" class="hidden rounded-lg border border-charcoal-200 px-4 py-2 text-sm font-medium hover:bg-charcoal-50 dark:border-navy-600 dark:hover:bg-navy-700">Kirim Ulang Email Atur Kata Sandi</button>
            <button id="resetPasswordBtn" type="button" class="hidden rounded-lg border border-charcoal-200 px-4 py-2 text-sm font-medium hover:bg-charcoal-50 dark:border-navy-600 dark:hover:bg-navy-700">Atur Ulang Kata Sandi</button>
            <button id="deleteUserBtn" type="button" class="hidden rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">Hapus Pengguna</button>
        </div>
    </div>

    @vite('resources/js/modules/user/show.js')
@endsection
