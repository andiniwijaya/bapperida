@extends('layouts.app')

@section('title', 'Registrasi Penomoran Surat')

@section('content')

    <div class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-bold">
                    Registrasi Penomoran Surat
                </h1>

                <p class="ds-subheading">
                    Tambah registrasi penomoran surat baru.
                </p>

            </div>

            <a href="{{ route('letter-number-registrations.index') }}" class="app-crud-back-link">
                Kembali
            </a>

        </div>

        <form id="registrationForm" data-form-ux>

            @include('letter-number-registrations.partials.form')

        </form>

    </div>

    @vite('resources/js/modules/letter-number-registration/create.js')

@endsection
