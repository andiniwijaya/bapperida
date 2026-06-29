<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends system_settings for enterprise configuration categories.
 *
 * Business rules:
 * - Letter, upload, dashboard, report, and activity-log settings are runtime-configurable.
 * - Defaults allow fresh installs to operate without manual DB edits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('app_name')->nullable()->after('id');

            $table->string('letter_prefix', 50)->nullable()->after('letter_number_template');
            $table->unsignedSmallInteger('active_year')->nullable()->after('letter_prefix');
            $table->unsignedInteger('letter_start_number')->default(1)->after('active_year');
            $table->string('default_letter_type', 50)->default('regular')->after('letter_start_number');
            $table->string('default_letter_priority', 50)->default('regular')->after('default_letter_type');

            $table->unsignedInteger('max_upload_size_kb')->default(10240)->after('default_letter_priority');
            $table->json('allowed_upload_mime_types')->nullable()->after('max_upload_size_kb');

            $table->unsignedSmallInteger('dashboard_default_period_days')->default(30)->after('allowed_upload_mime_types');
            $table->unsignedSmallInteger('dashboard_recent_activity_limit')->default(5)->after('dashboard_default_period_days');
            $table->unsignedSmallInteger('dashboard_table_row_limit')->default(10)->after('dashboard_recent_activity_limit');

            $table->string('report_signatory_name')->nullable()->after('dashboard_table_row_limit');
            $table->string('report_signatory_position')->nullable()->after('report_signatory_name');
            $table->string('report_logo')->nullable()->after('report_signatory_position');
            $table->text('report_footer')->nullable()->after('report_logo');

            $table->unsignedInteger('activity_log_retention_days')->nullable()->after('report_footer');
            $table->unsignedInteger('activity_log_max_export')->default(10000)->after('activity_log_retention_days');
            $table->boolean('activity_log_audit_enabled')->default(true)->after('activity_log_max_export');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'app_name',
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
            ]);
        });
    }
};
