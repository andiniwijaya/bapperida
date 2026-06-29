@extends('layouts.app')

@section('title', 'Detail Log Aktivitas')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header title="Detail Log Aktivitas" description="Informasi lengkap entri audit trail." />

        <input type="hidden" id="activity_log_id" value="{{ $activityLogId }}">
        <div id="activityLogDetail" class="app-crud-form-card app-crud-form-card--padded"></div>

        <x-button :href="route('admin.activity-logs.index')" variant="secondary">Kembali</x-button>
    </div>

    @vite('resources/js/modules/activity-log/show.js')
@endsection
