<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds singleton application configuration defaults.
 *
 * Configuration impact: initial values for all modules on fresh install.
 */
class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'BAPPERIDA Sistem Arsip Surat',

                'institution_name' => 'Badan Perencanaan Pembangunan, Riset dan Inovasi Daerah Kabupaten Bandung',
                'institution_short_name' => 'BAPPERIDA Kabupaten Bandung',

                'address' => 'Jl. Raya Soreang KM.17, Soreang, Kabupaten Bandung, Jawa Barat',
                'postal_code' => '40911',
                'phone' => '(022) 5891605',
                'email' => 'bapperida@bandungkab.go.id',
                'website' => 'https://bapperida.bandungkab.go.id',

                'logo' => 'logos/logo.png',
                'favicon' => 'logos/favicon.ico',

                'head_of_agency' => '',
                'head_position' => 'Kepala BAPPERIDA Kabupaten Bandung',
                'head_nip' => '',

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

                'copyright' => '© '.date('Y').' BAPPERIDA Kabupaten Bandung. Seluruh Hak Cipta Dilindungi.',

                'is_active' => true,
            ]
        );
    }
}
