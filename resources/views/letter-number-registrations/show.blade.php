@extends('layouts.app')

@section('title', 'Detail Registrasi Nomor Surat')

@section('content')

    <div class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-bold">
                    Detail Registrasi Nomor Surat
                </h1>

                <p class="ds-subheading">
                    Informasi lengkap registrasi penomoran surat.
                </p>

            </div>

            <a href="{{ route('letter-number-registrations.index') }}" class="app-crud-back-link">
                Kembali
            </a>

        </div>

        <input type="hidden" id="registration_id" value="{{ $registrationId }}">
        <div id="registrationDetail" class="space-y-6"></div>

    </div>

    @vite('resources/js/modules/letter-number-registration/show.js')

@endsection
