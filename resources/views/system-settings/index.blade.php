@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header title="Pengaturan Sistem" description="Konfigurasi aplikasi dan parameter operasional." />

        <form id="systemSettingForm" class="space-y-6" data-form-ux>
            <x-panel title="Informasi Umum">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6"><x-input id="app_name" name="app_name" label="Nama Aplikasi" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="institution_name" name="institution_name" label="Nama Institusi" required /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="institution_short_name" name="institution_short_name" label="Nama Singkat" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="email" name="email" type="email" label="Alamat Email" placeholder="Masukkan alamat email..." data-validate="email" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="phone" name="phone" label="Telepon" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="website" name="website" label="Website" /></div>
                    <div class="col-span-12"><x-input id="address" name="address" label="Alamat" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="city" name="city" label="Kota" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="postal_code" name="postal_code" label="Kode Pos" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="copyright" name="copyright" label="Copyright" /></div>
                </div>
            </x-panel>

            <x-panel title="Surat & Penomoran">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6"><x-input id="letter_prefix" name="letter_prefix" label="Prefix Surat" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="active_year" name="active_year" type="number" label="Tahun Aktif" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="letter_start_number" name="letter_start_number" type="number" label="Nomor Awal Surat" required /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="letter_number_template" name="letter_number_template" label="Template Nomor Surat" /></div>
                    <div class="col-span-12 md:col-span-6"><x-select id="default_letter_type" name="default_letter_type" label="Jenis Surat Default" :options="config('letter.types')" required /></div>
                    <div class="col-span-12 md:col-span-6"><x-select id="default_letter_priority" name="default_letter_priority" label="Prioritas Default" :options="config('letter.types')" required /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="head_of_agency" name="head_of_agency" label="Kepala Dinas" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="head_position" name="head_position" label="Jabatan" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="head_nip" name="head_nip" label="NIP" /></div>
                </div>
            </x-panel>

            <x-panel title="Unggah & Beranda">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-4"><x-input id="max_upload_size_kb" name="max_upload_size_kb" type="number" label="Batas Unggah (KB)" required /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="dashboard_default_period_days" name="dashboard_default_period_days" type="number" label="Periode Beranda (hari)" required /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="dashboard_recent_activity_limit" name="dashboard_recent_activity_limit" type="number" label="Limit Aktivitas Terbaru" required /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="dashboard_table_row_limit" name="dashboard_table_row_limit" type="number" label="Limit Baris Tabel" required /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="activity_log_retention_days" name="activity_log_retention_days" type="number" label="Retensi Log (hari)" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="activity_log_max_export" name="activity_log_max_export" type="number" label="Batas Ekspor Log" required /></div>
                </div>
            </x-panel>

            <x-panel title="Laporan & Sistem">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6"><x-input id="report_signatory_name" name="report_signatory_name" label="Nama Penandatangan Laporan" /></div>
                    <div class="col-span-12 md:col-span-6"><x-input id="report_signatory_position" name="report_signatory_position" label="Jabatan Penandatangan" /></div>
                    <div class="col-span-12"><x-input id="report_footer" name="report_footer" label="Footer Laporan" /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="timezone" name="timezone" label="Timezone" required /></div>
                    <div class="col-span-12 md:col-span-4"><x-input id="locale" name="locale" label="Locale" required /></div>
                    <div class="col-span-12 md:col-span-4 flex items-end gap-4">
                        <x-checkbox id="dark_mode_default" name="dark_mode_default" label="Mode gelap default" />
                        <x-checkbox id="activity_log_audit_enabled" name="activity_log_audit_enabled" label="Audit log aktif" />
                        <x-checkbox id="is_active" name="is_active" label="Sistem aktif" />
                    </div>
                </div>
            </x-panel>

            <div id="saveActions" class="hidden flex justify-end">
                <x-button type="submit">Simpan Pengaturan</x-button>
            </div>
        </form>
    </div>

    @vite('resources/js/modules/system-setting/index.js')
@endsection
