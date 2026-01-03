<?php

namespace Vocabia\LaravelSettings\Services;

use Illuminate\Support\Facades\Cache;
use DevoriaX\LaravelSettings\Models\Settings;
use Illuminate\Support\Facades\Config;

class SettingService
{
    public function get($key, $default = null)
    {
        $config = Config::get('settings.cache');

        if (!($config['enabled'] ?? false)) {
            return $this->getFromdb($key, $default);
        }

        $cacheKey = ($config['prefix'] ?? '') . $key;
        $store = $config['store'] ?? 'file';
        $ttl = $config['ttl'] ?? 3600;

        return Cache::store($store)->remember($cacheKey, $ttl, function () use ($key, $default) {
            return $this->getFromDb($key, $default);
        });
    }

    protected function getFromDb($key, $default)
    {
        $setting = Settings::query()->where('key', $key)->first();

        if (!$setting) return $default;

        return $setting->json_value ?? $setting->value;
    }

    public function clearCache($key): void
    {
        $prefix = Config::get('settings.cache.prefix', '');
        $store = Config::get('settings.cache.store', 'file');
        Cache::store($store)->forget($prefix . $key);
    }
}