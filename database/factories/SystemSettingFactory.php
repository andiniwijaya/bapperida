<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'app_name' => 'BAPPERIDA Sistem Arsip Surat',
            'institution_name' => 'Badan Perencanaan Pembangunan, Riset dan Inovasi Daerah Kabupaten Bandung',
            'institution_short_name' => 'BAPPERIDA Kabupaten Bandung',
            'address' => 'Jl. Raya Soreang KM.17',
            'city' => 'Bandung',
            'postal_code' => '40911',
            'phone' => '(022) 5891605',
            'email' => 'bapperida@bandungkab.go.id',
            'website' => 'https://bapperida.bandungkab.go.id',
            'logo' => null,
            'favicon' => null,
            'head_of_agency' => null,
            'head_position' => 'Kepala BAPPERIDA',
            'head_nip' => null,
            'letter_number_template' => '{letter_code}/{sequence_number}/{department}/{year}',
            'letter_prefix' => null,
            'active_year' => (int) date('Y'),
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
            'copyright' => '© BAPPERIDA',
            'is_active' => true,
        ];
    }
}
