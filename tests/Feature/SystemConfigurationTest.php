<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SystemSetting;
use App\Services\LetterNumberRegistration\PreviewLetterNumberService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

/**
 * Unit/integration tests for configuration-driven letter numbering and upload rules.
 */
class SystemConfigurationTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_letter_number_formatter_uses_configured_template(): void
    {
        SystemSetting::create(array_merge($this->systemSettingAttributes(), [
            'letter_number_template' => '{prefix}/{letter_code}-{sequence_number}',
            'letter_prefix' => 'BPRD',
        ]));

        app(SystemConfigurationService::class)->forgetCache();
        app(\App\Services\SystemSetting\GetSystemSettingService::class)->forgetCache();

        $department = Department::factory()->create(['code' => 'DPT']);

        $preview = app(PreviewLetterNumberService::class)->handle(
            letterCode: 'B',
            departmentId: $department->id,
            sequenceNumber: 5,
            year: 2026,
        );

        $this->assertSame('BPRD/B-005', $preview['letter_number']);
    }

    public function test_upload_rules_reflect_max_size_from_settings(): void
    {
        SystemSetting::create(array_merge($this->systemSettingAttributes(), [
            'max_upload_size_kb' => 2048,
        ]));

        app(\App\Services\SystemSetting\GetSystemSettingService::class)->forgetCache();

        $rules = app(SystemConfigurationService::class)->uploadFileRules(required: true);

        $this->assertContains('max:2048', $rules);
        $this->assertContains('mimes:pdf', $rules);
    }

    public function test_report_branding_falls_back_to_institution_fields(): void
    {
        SystemSetting::create(array_merge($this->systemSettingAttributes(), [
            'head_of_agency' => 'Dr. Kepala',
            'head_position' => 'Kepala Dinas',
            'copyright' => 'Footer instansi',
            'report_signatory_name' => null,
            'report_footer' => null,
        ]));

        app(\App\Services\SystemSetting\GetSystemSettingService::class)->forgetCache();

        $branding = app(SystemConfigurationService::class)->reportBranding();

        $this->assertSame('Dr. Kepala', $branding['signatory_name']);
        $this->assertSame('Kepala Dinas', $branding['signatory_position']);
        $this->assertSame('Footer instansi', $branding['footer']);
    }

    public function test_corrupted_settings_cache_is_reloaded_from_database(): void
    {
        $settings = SystemSetting::create($this->systemSettingAttributes());

        $getService = app(\App\Services\SystemSetting\GetSystemSettingService::class);

        Cache::forever(SystemConfigurationService::CACHE_KEY, new \stdClass());

        $resolved = $getService->handle();

        $this->assertInstanceOf(SystemSetting::class, $resolved);
        $this->assertSame($settings->id, $resolved->id);
        $this->assertSame($settings->id, Cache::get(SystemConfigurationService::CACHE_KEY));
    }
}
