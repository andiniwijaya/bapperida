@extends('layouts.app')

@section('title', 'Edit Registrasi Nomor Surat')

@section('content')

    <div class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-bold">
                    Ubah Registrasi Nomor Surat
                </h1>

                <p class="ds-subheading">
                    Perbarui data registrasi penomoran surat.
                </p>

            </div>

            <a href="{{ route('letter-number-registrations.index') }}" class="app-crud-back-link">
                Kembali
            </a>

        </div>

        <form id="registrationForm" data-form-ux>

            <input type="hidden" id="registration_id" value="{{ $registrationId }}">

            @include('letter-number-registrations.partials.form')

        </form>

    </div>

    @vite('resources/js/modules/letter-number-registration/edit.js')

@endsection
