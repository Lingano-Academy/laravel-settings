<?php

namespace Vocabia\LaravelSettings\Services;

use Illuminate\Support\Facades\Cache;
use Vocabia\LaravelSettings\Models\VocabiaSettings;
use Illuminate\Support\Facades\Config;

class VocabiaSettingService
{
    /**
     * Get settings using the key
     */
    public function get(string $key, $default = null)
    {
        $config = Config::get('settings.cache');

        // If cache is disabled, get directly from database
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

    /*
     * Save or update settings
     */
    public function set(string $key, $value, string $type = null, string $group = 'general'): VocabiaSettings
    {
        /** @var VocabiaSettings $setting */
        $setting = VocabiaSettings::query()->firstOrNew(['key' => $key]);

        // If it's a new record, set the group (if you added the group column)
        if (!$setting->exists) {
            $setting->group = $group;
        }

        // Use the model's smart method for storage
        $setting->setPayload($value, $type);

        // Clear old cache for this key
        $this->clearCache($key);

        return $setting;
    }

    /**
     * Delete settings
     */
    public function delete(string $key): bool
    {
        $deleted = VocabiaSettings::query()->where('key', $key)->delete();

        if ($deleted) {
            $this->clearCache($key);
        }

        return (bool)$deleted;
    }

    /**
     * Built-in method for reading from database
     */
    protected function getFromDb($key, $default)
    {
        $setting = VocabiaSettings::query()->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Important change: use payload instead of manually checking columns
        // The model decides itself whether to return a JSON value, encrypted or plain
        return $setting->payload;
    }

    /**
     * Clearing the cache of a specific key
     */
    public function clearCache($key): void
    {
        $config = Config::get('settings.cache');

        if ($config['enabled'] ?? false) {
            $prefix = $config['prefix'] ?? '';
            $store = $config['store'] ?? 'file';
            Cache::store($store)->forget($prefix . $key);
        }
    }
}