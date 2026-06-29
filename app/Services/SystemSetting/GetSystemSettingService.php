<?php

namespace App\Services\SystemSetting;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Retrieves the singleton system settings row with safe caching.
 *
 * Business rules:
 * - Application stores one active configuration row (id=1 from seeder).
 * - Cache is invalidated by UpdateSystemSettingService after changes.
 *
 * Configuration impact: all modules depend on cached settings from this service.
 */
class GetSystemSettingService
{
    /**
     * Return cached system settings or load from database.
     */
    public function handle(): SystemSetting
    {
        $cachedId = Cache::get(SystemConfigurationService::CACHE_KEY);

        if (is_scalar($cachedId)) {
            $settings = SystemSetting::query()->find($cachedId);

            if ($settings instanceof SystemSetting) {
                return $settings;
            }

            Cache::forget(SystemConfigurationService::CACHE_KEY);
        } elseif ($cachedId !== null) {
            Cache::forget(SystemConfigurationService::CACHE_KEY);
        }

        $settings = SystemSetting::query()->first();

        if ($settings === null) {
            $settings = SystemSetting::factory()->create();
        }

        Cache::forever(SystemConfigurationService::CACHE_KEY, $settings->getKey());

        return $settings;
    }

    /**
     * Clear cached settings after an update.
     */
    public function forgetCache(): void
    {
        Cache::forget(SystemConfigurationService::CACHE_KEY);
    }
}
