<?php

namespace Tests\Concerns;

use App\Models\SystemSetting;

/**
 * Shared system setting payloads for feature tests.
 */
trait CreatesSystemSettings
{
    /**
     * @return array<string, mixed>
     */
    protected function systemSettingAttributes(): array
    {
        return [
            'app_name' => 'BAPPERIDA Sistem Arsip Surat',
            'institution_name' => 'BAPPERIDA Kabupaten Bandung',
            'institution_short_name' => 'BAPPERIDA',
            'address' => 'Alamat',
            'city' => 'Bandung',
            'postal_code' => '40123',
            'phone' => '022123456',
            'email' => 'info@bapperida.go.id',
            'website' => 'https://bapperida.go.id',
            'logo' => null,
            'favicon' => null,
            'head_of_agency' => 'Kepala',
            'head_position' => 'Kepala Dinas',
            'head_nip' => '123456789',
            'letter_number_template' => '{letter_code}/{sequence_number}/{department}/{year}',
            'letter_prefix' => null,
            'active_year' => 2026,
            'letter_start_number' => 1,
            'default_letter_type' => 'regular',
            'default_letter_priority' => 'regular',
            'max_upload_size_kb' => 10240,
            'allowed_upload_mime_types' => ['pdf'],
            'dashboard_default_period_days' => 30,
            'dashboard_recent_activity_limit' => 5,
            'dashboard_table_row_limit' => 10,
            'report_signatory_name' => null,
            'report_signatory_position' => null,
            'report_logo' => null,
            'report_footer' => null,
            'activity_log_retention_days' => 365,
            'activity_log_max_export' => 10000,
            'activity_log_audit_enabled' => true,
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id',
            'dark_mode_default' => false,
            'copyright' => 'Copyright',
            'is_active' => true,
        ];
    }

    protected function createSystemSettingRecord(): SystemSetting
    {
        return SystemSetting::create($this->systemSettingAttributes());
    }
}
