<?php

namespace Vocabia\LaravelSettings\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Vocabia\LaravelSettings\Models\VocabiaSettings;

class VocabiaSettingService
{
    public function get(string $key, $default = null)
    {
        $config = Config::get('vocabia_settings.cache', []);

        if (!($config['enabled'] ?? false)) {
            return $this->getFromDb($key, $default);
        }

        $cacheKey = ($config['prefix'] ?? '') . $key;
        $store = $config['store'] ?? 'file';
        $ttl = $config['ttl'] ?? 3600;

        return Cache::store($store)->remember($cacheKey, $ttl, function () use ($key, $default) {
            return $this->getFromDb($key, $default);
        });
    }

    public function set(string $key, $value, string $type = null, string $group = 'general'): VocabiaSettings
    {
        /** @var VocabiaSettings $setting */
        $setting = VocabiaSettings::query()->firstOrNew(['key' => $key]);

        if (! $setting->exists) {
            $setting->group = $group;
        }

        $setting->setPayload($value, $type);

        $this->clearCache($key);

        return $setting;
    }

    public function delete(string $key): bool
    {
        $deleted = VocabiaSettings::query()->where('key', $key)->delete();

        if ($deleted) {
            $this->clearCache($key);
        }

        return (bool) $deleted;
    }

    protected function getFromDb($key, $default)
    {
        $setting = VocabiaSettings::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->payload;
    }

    public function clearCache($key): void
    {
        $config = Config::get('vocabia_settings.cache', []);

        if ($config['enabled'] ?? false) {
            $prefix = $config['prefix'] ?? '';
            $store = $config['store'] ?? 'file';
            Cache::store($store)->forget($prefix . $key);
        }
    }
}