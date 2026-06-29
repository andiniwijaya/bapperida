<?php

namespace App\Http\Requests\Api\SystemSetting;

use App\Models\SystemSetting;
use App\Services\SystemSetting\GetSystemSettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates system configuration updates from superadmin.
 *
 * Business rules:
 * - authorize() requires update permission on singleton settings row.
 * - Letter type and priority must match config/letter.types keys.
 *
 * Configuration impact: validated values drive runtime module behavior after cache refresh.
 */
class UpdateSystemSettingRequest extends FormRequest
{
    /**
     * Requires superadmin update permission on SystemSetting singleton.
     */
    public function authorize(): bool
    {
        $systemSetting = app(GetSystemSettingService::class)->handle();

        return $this->user()?->can('update', $systemSetting) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $letterTypes = array_keys(config('letter.types'));

        return [
            'app_name' => ['nullable', 'string', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_short_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'favicon' => ['nullable', 'string', 'max:255'],
            'head_of_agency' => ['nullable', 'string', 'max:255'],
            'head_position' => ['nullable', 'string', 'max:255'],
            'head_nip' => ['nullable', 'string', 'max:30'],
            'letter_number_template' => ['nullable', 'string', 'max:255'],
            'letter_prefix' => ['nullable', 'string', 'max:50'],
            'active_year' => ['nullable', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'letter_start_number' => ['required', 'integer', 'min:1'],
            'default_letter_type' => ['required', 'string', Rule::in($letterTypes)],
            'default_letter_priority' => ['required', 'string', Rule::in($letterTypes)],
            'max_upload_size_kb' => ['required', 'integer', 'min:1', 'max:51200'],
            'allowed_upload_mime_types' => ['required', 'array', 'min:1'],
            'allowed_upload_mime_types.*' => ['required', 'string', 'max:20'],
            'dashboard_default_period_days' => ['required', 'integer', 'min:1', 'max:365'],
            'dashboard_recent_activity_limit' => ['required', 'integer', 'min:1', 'max:50'],
            'dashboard_table_row_limit' => ['required', 'integer', 'min:1', 'max:100'],
            'report_signatory_name' => ['nullable', 'string', 'max:255'],
            'report_signatory_position' => ['nullable', 'string', 'max:255'],
            'report_logo' => ['nullable', 'string', 'max:255'],
            'report_footer' => ['nullable', 'string'],
            'activity_log_retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'activity_log_max_export' => ['required', 'integer', 'min:100', 'max:100000'],
            'activity_log_audit_enabled' => ['required', 'boolean'],
            'timezone' => ['required', 'string', 'max:100'],
            'locale' => ['required', 'string', 'max:10'],
            'dark_mode_default' => ['required', 'boolean'],
            'copyright' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
