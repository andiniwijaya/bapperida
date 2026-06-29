<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of application configuration grouped by category.
 *
 * Configuration impact: exposes all runtime settings for admin configuration UI.
 */
class SystemSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'general' => [
                'app_name' => $this->app_name,
                'institution_name' => $this->institution_name,
                'institution_short_name' => $this->institution_short_name,
                'address' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'phone' => $this->phone,
                'email' => $this->email,
                'website' => $this->website,
                'logo' => $this->logo,
                'favicon' => $this->favicon,
                'timezone' => $this->timezone,
                'locale' => $this->locale,
                'dark_mode_default' => $this->dark_mode_default,
                'copyright' => $this->copyright,
                'is_active' => $this->is_active,
            ],
            'letter' => [
                'letter_number_template' => $this->letter_number_template,
                'letter_prefix' => $this->letter_prefix,
                'active_year' => $this->active_year,
                'letter_start_number' => $this->letter_start_number,
                'default_letter_type' => $this->default_letter_type,
                'default_letter_priority' => $this->default_letter_priority,
                'head_of_agency' => $this->head_of_agency,
                'head_position' => $this->head_position,
                'head_nip' => $this->head_nip,
            ],
            'upload' => [
                'max_upload_size_kb' => $this->max_upload_size_kb,
                'allowed_upload_mime_types' => $this->allowed_upload_mime_types ?? ['pdf'],
            ],
            'dashboard' => [
                'dashboard_default_period_days' => $this->dashboard_default_period_days,
                'dashboard_recent_activity_limit' => $this->dashboard_recent_activity_limit,
                'dashboard_table_row_limit' => $this->dashboard_table_row_limit,
            ],
            'report' => [
                'report_signatory_name' => $this->report_signatory_name,
                'report_signatory_position' => $this->report_signatory_position,
                'report_logo' => $this->report_logo,
                'report_footer' => $this->report_footer,
            ],
            'activity_log' => [
                'activity_log_retention_days' => $this->activity_log_retention_days,
                'activity_log_max_export' => $this->activity_log_max_export,
                'activity_log_audit_enabled' => $this->activity_log_audit_enabled,
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'can' => [
                'update' => $request->user()?->can('update', $this->resource) ?? false,
            ],
        ];
    }
}
