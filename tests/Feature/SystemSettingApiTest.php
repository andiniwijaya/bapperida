<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SystemSetting\GetSystemSettingService;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesSystemSettings;
use Tests\TestCase;

/**
 * Feature tests for system settings API and configuration service.
 */
class SystemSettingApiTest extends TestCase
{
    use CreatesSystemSettings;
    use RefreshDatabase;

    public function test_guest_cannot_access_system_setting(): void
    {
        $this->getJson('/api/system-settings')->assertStatus(401);
    }

    public function test_staff_cannot_view_system_setting(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->createSystemSettingRecord();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/system-settings')
            ->assertStatus(403);
    }

    public function test_admin_cannot_view_system_setting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createSystemSettingRecord();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/system-settings')
            ->assertForbidden();
    }

    public function test_superadmin_can_view_system_setting(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $systemSetting = $this->createSystemSettingRecord();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/system-settings');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $systemSetting->id);
        $response->assertJsonPath('data.general.institution_name', 'BAPPERIDA Kabupaten Bandung');
        $response->assertJsonStructure([
            'data' => [
                'general',
                'letter',
                'upload',
                'dashboard',
                'report',
                'activity_log',
            ],
        ]);
    }

    public function test_superadmin_can_update_system_setting_and_records_activity_log(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $systemSetting = $this->createSystemSettingRecord();

        $payload = array_merge($this->systemSettingAttributes(), [
            'institution_name' => 'BAPPERIDA Updated',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/system-settings', $payload);

        $response->assertOk();
        $response->assertJsonPath('data.general.institution_name', 'BAPPERIDA Updated');
        $response->assertJsonPath('data.general.is_active', false);

        $this->assertDatabaseHas('system_settings', [
            'id' => $systemSetting->id,
            'institution_name' => 'BAPPERIDA Updated',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'module' => 'system_setting',
            'action' => 'setting_updated',
        ]);
    }

    public function test_admin_cannot_update_system_setting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createSystemSettingRecord();

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/system-settings', $this->systemSettingAttributes())
            ->assertStatus(403);
    }

    public function test_get_system_setting_service_reloads_model_from_database(): void
    {
        $settings = $this->createSystemSettingRecord();
        $getService = app(GetSystemSettingService::class);

        $first = $getService->handle();

        $settings->update(['institution_name' => 'Changed In Database']);

        $second = $getService->handle();

        $this->assertSame($first->id, $second->id);
        $this->assertSame('Changed In Database', $second->institution_name);
        $this->assertSame($settings->id, Cache::get(SystemConfigurationService::CACHE_KEY));
    }

    public function test_update_clears_configuration_cache(): void
    {
        Cache::flush();
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->createSystemSettingRecord();

        $configuration = app(SystemConfigurationService::class);
        $configuration->settings();

        $payload = array_merge($this->systemSettingAttributes(), [
            'max_upload_size_kb' => 5120,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/system-settings', $payload)
            ->assertOk();

        $this->assertSame(5120, app(SystemConfigurationService::class)->settings()->max_upload_size_kb);
    }
}
