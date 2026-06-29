<?php

namespace App\Services\SystemSetting;

use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Single source of truth for runtime application configuration.
 *
 * Business rules:
 * - All modules must read settings through this service (not direct model queries).
 * - Values are cached until UpdateSystemSettingService invalidates the cache.
 * - Report branding falls back to general institution fields when report-specific fields are empty.
 */
class SystemConfigurationService
{
    public const CACHE_KEY = 'system_settings.singleton';

    public function __construct(private GetSystemSettingService $getService) {}

    /**
     * Cached singleton system setting row.
     */
    public function settings(): SystemSetting
    {
        return $this->getService->handle();
    }

    /**
     * Laravel validation rules for letter PDF uploads.
     *
     * @return array<int, string>
     */
    public function uploadFileRules(bool $required = true): array
    {
        $settings = $this->settings();
        $mimes = $settings->allowed_upload_mime_types ?? ['pdf'];
        $maxKb = (int) ($settings->max_upload_size_kb ?? 10240);

        $rules = ['file'];

        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        $rules[] = 'mimes:'.implode(',', $mimes);
        $rules[] = 'max:'.$maxKb;

        return $rules;
    }

    /**
     * @return array{period_start: string, period_end: string}
     */
    public function dashboardDefaultPeriod(): array
    {
        $days = (int) ($this->settings()->dashboard_default_period_days ?? 30);

        return [
            'period_start' => now()->subDays($days - 1)->toDateString(),
            'period_end' => now()->toDateString(),
        ];
    }

    public function dashboardRecentActivityLimit(): int
    {
        return (int) ($this->settings()->dashboard_recent_activity_limit ?? 5);
    }

    public function dashboardTableRowLimit(): int
    {
        return (int) ($this->settings()->dashboard_table_row_limit ?? 10);
    }

    public function activeYear(): int
    {
        return (int) ($this->settings()->active_year ?? now()->year);
    }

    public function letterStartNumber(): int
    {
        return max(1, (int) ($this->settings()->letter_start_number ?? 1));
    }

    public function letterNumberTemplate(): string
    {
        return $this->settings()->letter_number_template
            ?? '{letter_code}/{sequence_number}/{department}/{year}';
    }

    public function letterPrefix(): ?string
    {
        $prefix = $this->settings()->letter_prefix;

        return $prefix !== null && $prefix !== '' ? $prefix : null;
    }

    public function defaultLetterType(): string
    {
        return $this->settings()->default_letter_type ?? 'regular';
    }

    public function defaultLetterPriority(): string
    {
        return $this->settings()->default_letter_priority ?? 'regular';
    }

    /**
     * @return array{
     *     institution_name: string,
     *     institution_short_name: string|null,
     *     address: string|null,
     *     logo_path: string|null,
     *     signatory_name: string|null,
     *     signatory_position: string|null,
     *     footer: string|null
     * }
     */
    /**
     * Branding payload for transactional email templates.
     *
     * @return array{
     *     app_name: string,
     *     institution_name: string,
     *     institution_logo_url: string,
     *     kab_bandung_logo_url: string,
     *     bapperida_logo_url: string
     * }
     */
    public function emailBranding(): array
    {
        $settings = $this->settings();

        return [
            'app_name' => $settings->app_name ?: config('app.name'),
            'institution_name' => $settings->institution_name,
            'institution_logo_url' => $this->resolveEmailLogoUrl(
                $settings->logo ?? $settings->report_logo
            ),
            'kab_bandung_logo_url' => asset('assets/images/logo-kab-bandung.png'),
            'bapperida_logo_url' => asset('assets/images/logo-bapperida.png'),
        ];
    }

    public function reportBranding(): array
    {
        $settings = $this->settings();

        return [
            'institution_name' => $settings->institution_name,
            'institution_short_name' => $settings->institution_short_name,
            'address' => $settings->address,
            'phone' => $settings->phone,
            'email' => $settings->email,
            'website' => $settings->website,
            'logo_path' => $this->resolvePublicAssetPath(
                $settings->report_logo ?? $settings->logo
            ),
            'signatory_name' => $settings->report_signatory_name ?? $settings->head_of_agency,
            'signatory_position' => $settings->report_signatory_position ?? $settings->head_position,
            'footer' => $settings->report_footer ?? $settings->copyright,
        ];
    }

    public function activityLogMaxExport(): int
    {
        return max(1, (int) ($this->settings()->activity_log_max_export ?? 10000));
    }

    public function activityLogRetentionDays(): ?int
    {
        $days = $this->settings()->activity_log_retention_days;

        return $days !== null ? (int) $days : null;
    }

    public function activityLogAuditEnabled(): bool
    {
        return (bool) ($this->settings()->activity_log_audit_enabled ?? true);
    }

    /**
     * Invalidate cached configuration after settings update.
     */
    public function forgetCache(): void
    {
        $this->getService->forgetCache();
    }

    private function resolveEmailLogoUrl(?string $path): string
    {
        if ($path === null || $path === '') {
            return asset('assets/images/logo-kab-bandung.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function resolvePublicAssetPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return public_path('assets/images/logo-kab-bandung.png');
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return public_path($path);
    }
}
