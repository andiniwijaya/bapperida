<?php

namespace App\Models;

use Database\Factories\SystemSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Singleton application configuration record.
 *
 * Business rules:
 * - One row holds all runtime settings; seeded with id=1 on install.
 * - Read via SystemConfigurationService; updated only by superadmin.
 * - Snapshots letter, upload, dashboard, report, and audit-log preferences.
 *
 * Configuration impact: drives validation rules, branding, and module defaults.
 */
class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory;

    /**
     * Mass-assignable configuration fields grouped by category in API resource.
     */
    protected $fillable = [
        'app_name',
        'institution_name',
        'institution_short_name',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'logo',
        'favicon',
        'head_of_agency',
        'head_position',
        'head_nip',
        'letter_number_template',
        'letter_prefix',
        'active_year',
        'letter_start_number',
        'default_letter_type',
        'default_letter_priority',
        'max_upload_size_kb',
        'allowed_upload_mime_types',
        'dashboard_default_period_days',
        'dashboard_recent_activity_limit',
        'dashboard_table_row_limit',
        'report_signatory_name',
        'report_signatory_position',
        'report_logo',
        'report_footer',
        'activity_log_retention_days',
        'activity_log_max_export',
        'activity_log_audit_enabled',
        'timezone',
        'locale',
        'dark_mode_default',
        'copyright',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dark_mode_default' => 'boolean',
            'is_active' => 'boolean',
            'activity_log_audit_enabled' => 'boolean',
            'allowed_upload_mime_types' => 'array',
            'active_year' => 'integer',
            'letter_start_number' => 'integer',
            'max_upload_size_kb' => 'integer',
            'dashboard_default_period_days' => 'integer',
            'dashboard_recent_activity_limit' => 'integer',
            'dashboard_table_row_limit' => 'integer',
            'activity_log_retention_days' => 'integer',
            'activity_log_max_export' => 'integer',
        ];
    }
}
